<?php

class Model_DbTable_EventAttendees extends Zend_Db_Table_Abstract
{
    protected $_name = 'EventAttendee';
    protected $_primary = array('event_id', 'user_id');
    protected $_rowClass = 'Model_EventAttendee';

    public static function getEventAttendees(Model_Event $event, $waitingList = false)
    {
        $eventAttendees = new self();
        $attendees = $eventAttendees->fetchAll( $eventAttendees->select()->order(array('time ASC', 'microtime ASC'))->where('waiting_list = ' . ($waitingList ? 1 : 0))->where('event_id = ?', $event->id) );
        return $attendees;
    }

    public static function getEventAttendee(Model_Event $event, Model_User $user)
    {
        $eventAttendees = new self();
        $attendee = $eventAttendees->fetchRow( $eventAttendees->select()->where('event_id = ?', $event->id)->where('user_id = ?', $user->id) );
        return $attendee;
    }

    public static function getEventAttendeeById(Model_Event $event, $user_id)
    {
        $eventAttendees = new self();
        $attendee = $eventAttendees->fetchRow( $eventAttendees->select()->where('event_id = ?', $event->id)->where('user_id = ?', $user_id) );
        return $attendee;
    }

    public static function getEventWaitingList(Model_Event $event)
    {
        return self::getEventAttendees($event, true);
    }

    public static function getEventsNextInWaitingList(Model_Event $event)
    {
        $eventAttendees = new self();
        $attendee = $eventAttendees->fetchRow( $eventAttendees->select()->order(array('time ASC', 'microtime ASC'))->where('event_id = ?', $event->id)->where('waiting_list = 1')->limit(1, 0) );
        return $attendee;
    }

    public static function isAttendee(Model_Event $event, Model_User $user, $waitingList = false)
    {
        $eventAttendees = new self();
        $attendee = $eventAttendees->fetchRow( $eventAttendees->select()->where('waiting_list = ' . ($waitingList ? 1 : 0))->where('event_id = ?', $event->id)->where('user_id = ?', $user->id) );
        return $attendee == true;
    }

    public static function isOnWaitingList(Model_Event $event, Model_User $user)
    {
        return self::isUserAttendee($event, $user, true);
    }

    public static function addAttendee(Model_Event $event, Model_User $user)
    {
        // Record the time (+micro float) as soon as possible to secure spot within second.
        $timeParts = explode(' ', microtime());
        $time = (int) $timeParts[1];
        $microtime = (float) $timeParts[0];

        $spotsLeft = ($event->participants_max == 0 ? 1 : $event->participants_max - $event->participants_current);

        $eventAttendees = new self();
        $attendee = $eventAttendees->createRow();

        $attendee->event_id = $event->id;
        $attendee->user_id  = $user->id;
        $attendee->time     = $time;
        $attendee->microtime = $microtime;
        $attendee->waiting_list = ($spotsLeft <= 0 ? 1 : 0);
        $attendee->save();

        if ($attendee->waiting_list == 1) return $attendee;

        // Lets reserve our spot in the event
        $event->participants_current = new Zend_Db_Expr('participants_current + 1');
        $event->save();

        if ($event->participants_max != 0) {
            // Check if we got a spot!
            // Neccessary to catch race-conditions
            //if ($event->participants_current > $event->participants_max) {
                $mySpot = self::getAttendeeSpot($event, $user);
                // Woops! We were'nt first, and have to remove our spot again.
                if ($mySpot > $event->participants_max) {
                    $event->participants_current = new Zend_Db_Expr('participants_current - 1');
                    $event->save();
                    $attendee->waiting_list = 1;
                    $attendee->save();
                }
            //}
        }

        return $attendee;
    }

    public static function removeAttendee(Model_Event $event, Model_User $user)
    {
        $attendee = self::getEventAttendee($event, $user);
        if (!$attendee) return;

        $waitingList = $attendee->isOnWaitingList();
        $attendee->delete();

        // If we're on the waiting list there is nothing to change
        if ($waitingList) return true;

        $event->participants_current = new Zend_Db_Expr('participants_current - 1');
        $event->save();

        self::moveUpWaitingList($event);

        return true;
    }

    public static function getAttendeeSpot(Model_Event $event, Model_User $user)
    {
        $attendees = self::getEventAttendees($event);
        foreach ($attendees as $i => $attendee) {
            if ($attendee->user_id == $user->id) {
                return $i + 1;
            }
        }
        return 99999999;
    }

    public static function getAttendeeSpotOnWaitingList(Model_EventAttendee $attendee)
    {
        $event = Model_DbTable_Events::getEvent($attendee->event_id);
        $attendees = self::getEventWaitingList($event);
        foreach ($attendees as $i => $att) {
            if ($att->user_id == $attendee->user_id) {
                return $i + 1;
            }
        }
        return 99999999;
    }

    public static function getUserEvents(Model_User $user)
    {
        if (!$user) throw new Exception('Requires a user object');

        $eventAttendees = new self();
        $attendsEvents = $eventAttendees->fetchAll( $eventAttendees->select()->where('user_id = ' . $user->id) );

        return $attendsEvents;
    }

    public static function removeAttendeeFromAllEvents(Model_User $user)
    {
        foreach (self::getUserEvents($user) as $e) {
            $event = Model_DbTable_Events::getEvent($e->event_id);
            self::removeAttendee($event, $user);
        }
    }

    public static function moveUpWaitingList(Model_Event $event)
    {
        $current = $event->participants_current;
        $max     = $event->participants_max;

        for ($i = $current; $i < $max; $i++) {

            // If there are no free spaces we check if there's anyone in the waiting list we can promote
            $userWaiting = self::getEventsNextInWaitingList($event);
            if ($userWaiting) {

                // Lock down the spot as early as possible
                $event->participants_current = new Zend_Db_Expr('participants_current + 1');
                $event->save();

                // Make the spot a non-waiting list spot
                $userWaiting->waiting_list = 0;
                $userWaiting->save();

                // This user is now an attendee, no longer on the waiting list
                //$u = $userWaiting->getUser();
                //$userWaiting->delete(); // Delete the old attendee record
                //self::addAttendee($event, $u);

                // Let him know by email
                self::sendEmailUpdateNoLongerWaitingList($event, $userWaiting->getUser());

            }else break;

        }
    }

    public static function sendEmailUpdateNoLongerWaitingList(Model_Event $event, Model_User $user)
    {
        if (!$event) throw new Exception('Must have a valid event object');
        if (!$user) throw new Exception('Must have a valid user object');

        // Render new user email template
        $html = new Zend_View();
        $html->setScriptPath(APPLICATION_PATH . '/views/email/');
        $html->assign('eventName', $event->title);
        $html->assign('eventId', $event->id);

        // Send the new password to the user in an email
        $mail = new Zend_Mail('utf-8');
        $mail->setMessageId();
        $mail->addTo($user->email);
        //$mail->addTo('kristoffer@elv.technology');
        $mail->setSubject('Du er flyttet opp fra ventelisten på et arrangement fra Mannhullet');
        $mail->setFrom('no-reply@mannhullet.no','Mannhullet.no');

        $mail->setBodyText('Du har blitt flyttet opp fra ventelisten til arrangementet ' . $event->title . ', og er nå påmeldt som deltaker. http://' . $_SERVER['HTTP_HOST'] . '/arrangement/' . $event->id);
        $mail->setBodyHtml($html->render('eventnolongeronwaitinglist.phtml'));

        $mail->send();
    }
}
