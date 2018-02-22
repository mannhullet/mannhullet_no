<?php

class IndexController extends Zend_Controller_Action
{

    protected $user = null;

    public function init()
    {
        $this->view->headerNav = '/';

        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = $this->user = $this->view->user = (isset($userSession->user) ? $userSession->user : false);

        // Used for the groups-menu
        $groups = $this->view->groups = Model_DbTable_Groups::getGroups();

        Model_DbTable_Sessions::persistUserId();

        $this->view->socialIconFacebook = Model_DbTable_Partials::getHtml('/footer:socialIconFacebook'); //'https://www.facebook.com/groups/2217435004/';
        $this->view->socialIconTwitter = Model_DbTable_Partials::getHtml('/footer:socialIconTwitter'); //'https://twitter.com/search?q=mannhullet&src=typd';

        $sponsor = $this->view->sponsor = Model_DbTable_Sponsors::getSponsorRandom();
    }

    public function indexAction()
    {
        $this->view->mainnav = '/';

        $this->view->slides = Model_DbTable_Carousel::getSlides();

        $pageNum = $this->view->pageNum = $this->_getParam('page', 1);
        $news = $this->view->news = Model_DbTable_News::getNews($pageNum);
        $pageCount = $this->view->pageCount = Model_DbTable_News::getPageCount();

        $events = $this->view->events = Model_DbTable_Events::getEventsList(false, 5, null, ($this->user == true));
    }

    public function nyhetAction()
    {
        $this->view->mainnav = '/nyheter';
        $this->view->headTitle('Nyheter');

        $aid = $this->_getParam('aid', -1);
        $article = $this->view->article = Model_DbTable_News::getArticle($aid);

        $this->view->headTitle($article->title, 'PREPEND');
    }

    public function omAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
    }

    public function omhistorieAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
        $this->view->headTitle('Historie', 'PREPEND');
    }

    public function omstatutterAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
        $this->view->headTitle('Statutter', 'PREPEND');
    }

    public function omstyretAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
        $this->view->headTitle('Styret', 'PREPEND');

        $persons = $this->view->persons = Model_DbTable_AboutPersons::getPersons('styret');
    }

    public function omundergruppesjeferAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
        $this->view->headTitle('Undergruppesjefer', 'PREPEND');

        $persons = $this->view->persons = Model_DbTable_AboutPersons::getPersons('gruppesjefer');
    }

    public function omsprktrAction()
    {
        $this->view->mainnav = '/om';
        $this->view->headTitle('Om oss');
        $this->view->headTitle('SPR/KTR', 'PREPEND');

        $personsSpr = $this->view->personsSpr = Model_DbTable_AboutPersons::getPersons('spr');
        $personsKtr = $this->view->personsKtr = Model_DbTable_AboutPersons::getPersons('ktr');
    }

    public function arrangementAction()
    {
        $this->view->mainnav = '/arrangement';
        $this->view->headTitle('Arrangementer');

        // Find out what month of what year we're displaying
        $month = $this->_getParam('month', -1);
        if (!is_numeric($month)) throw new Exception('Invalid input');
        if ($month < 1 || $month > 12) $month = date('n');
        $this->view->month = $month;

        $year = $this->_getParam('year', -1);
        if (!is_numeric($year)) throw new Exception('Invalid input');
        if ($year < 1970) $year = date('Y');
        $this->view->year = $year;

        $this->view->previousMonthParam = '?month=' . ($month-1 < 1 ? '12&year=' . ($year-1) : ($month-1) . (date('Y') != $year ? '&year=' . $year : ''));
        $this->view->nextMonthParam = '?month=' . ($month+1 > 12 ? '1&year=' . ($year+1) : ($month+1) . (date('Y') != $year ? '&year=' . $year : ''));

        $this->view->isCurrentMonth = (date('Y') == $year && date('n') == $month ? true : false);
        $this->view->dateFormat = 'j/n';

        $events = $this->view->events = Model_DbTable_Events::getEventsByMonth($month, $year, ($this->user == true));

        if (!$this->view->isCurrentMonth) {
            $cMT = $this->view->currentMonthTitle = str_replace(array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ), array(
                'Januar',
                'Februar',
                'Mars',
                'April',
                'Mai',
                'Juni',
                'Juli',
                'August',
                'September',
                'Oktober',
                'November',
                'Desember'
            ), date('F', strtotime($year.'-'.$month.'-01')));
        }
    }

    public function arrangementlistAction()
    {
        $this->view->mainnav = '/arrangement/list';
        $this->view->headTitle('Arrangementer');

        $this->view->isCurrentWeek = true;

        $events = $this->view->events = Model_DbTable_Events::getEventsList(false, 15, null, ($this->user == true));
        //print_r($events);die();
    }

    public function arrangementbyidAction()
    {
        $this->view->mainnav = '/arrangement';
        $this->view->headTitle('Arrangementer');

        $aid = $this->_getParam('aid', -1);
        $event = $this->view->event = Model_DbTable_Events::getEvent($aid);

        if ($event->hidden_from_public && $this->user != true) throw new Exception('User not allowed to see event');

        // If we're an administrator we want to show them who's payed and
        // administrating it.
        if ($this->user && ($this->user->admin || ($event->isGroupEvent() && $event->getGroup()->isAdmin($this->user)))) $this->view->userIsSiteOrOwningGroupAdmin = true;

        $attendees = $this->view->attendees = $event->getAttendees();
        //if ($this->user && $this->user->email == 'kristoiv@stud.ntnu.no') {$test = $attendees->toArray(); var_dump(count($test)); array_unique($test); var_dump(count($test)); die();}
        $waitingList = $this->view->waitingList = $event->getWaitingList();

        $userIsAttendee = $this->view->userIsAttendee = false;
        foreach ($attendees as $attendee) if ($this->user && $attendee->user_id == $this->user->id) {$userIsAttendee = $this->view->userIsAttendee = true;break;}

        $place = 0;
        $userIsOnWaitingList = $this->view->userIsOnWaitingList = false;
        foreach ($waitingList as $attendee) {
            $place++;
            if ($attendee->user_id == $this->user->id) {
                $userIsOnWaitingList = $this->view->userIsOnWaitingList = true;
                $waitingListSpot = $this->view->waitingListSpot = $place;
                break;
            }
        }

        $this->view->headTitle($event->title, 'PREPEND');

        if ($this->getRequest()->isPost()) {

            if (!$this->user) throw new Exception('Only logged in users may join events');

            $act = $this->_getParam('act', false);

            if ($act == 'payedup') {

                if (!$this->view->userIsSiteOrOwningGroupAdmin) throw new Exception('Not allowed to change this');

                $user_id = $this->_getParam('user_id', -1);
                $attendee = Model_DbTable_EventAttendees::getEventAttendeeById($event, $user_id);
                $attendee->payed = 1;
                $attendee->save();

                // Return a successfull response
                header('Content-Type: application/json');
                die(json_encode(array('status' => 'Success')));

            }else if ($act == 'notPayedup') {

                if (!$this->view->userIsSiteOrOwningGroupAdmin) throw new Exception('Not allowed to change this');

                $user_id = $this->_getParam('user_id', -1);
                $attendee = Model_DbTable_EventAttendees::getEventAttendeeById($event, $user_id);
                $attendee->payed = 0;
                $attendee->save();

                // Return a successfull response
                header('Content-Type: application/json');
                die(json_encode(array('status' => 'Success')));

            }else{

                if ($event->isSignupOpen()) {
                    if ($userIsAttendee || $userIsOnWaitingList) {
                        $event->removeAttendee($this->user);
                    }else{
                        $event->addAttendee($this->user);
                    }
                }
                return $this->_redirect('/arrangement/' . $event->id);

            }
        }
    }

    public function gruppeAction()
    {
        $this->view->headTitle('Grupper');
        $this->view->mainnav = '/gruppe';

        $gid = $this->_getParam('gid', -1);
        $group = $this->view->group = Model_DbTable_Groups::getGroup($gid);

        $this->view->secondarynav = '/gruppe/' . $group->id;
        $this->view->headTitle($group->title, 'PREPEND');

        $groupPages = $this->view->groupPages = Model_DbTable_GroupPages::getGroupPages($gid);

        $pid = $this->_getParam('pid', -1);
        if ($pid <= 0 ) {

            $this->view->isGroupFront = true;
            $pageNum = $this->view->pageNum = $this->_getParam('page', 1);
            $news = $this->view->news = Model_DbTable_News::getNews($pageNum, 5, $group);
            $pageCount = $this->view->pageCount = Model_DbTable_News::getPageCount(5, $group);

            $events = $this->view->events = Model_DbTable_Events::getEventsList(false, 15, $group, ($this->user == true));

        }else{
            $this->view->isGroupFront = false;
            $page = $this->view->page = Model_DbTable_GroupPages::getGroupPage($pid);
        }
    }

    public function registrerAction()
    {
        if ($this->user) return $this->_redirect('/');

        $this->view->mainnav = '/registrer';
        $this->view->headTitle('Registrer deg');

        $act = $this->_getParam('act', '');
        if ($act == 'verifyemail') {
            $code = $this->_getParam('code', '');
            if (!Model_DbTable_Users::verifyEmail($code)) {
                $this->view->message = 'Fant ikke verifisjonskoden i databasen. Administratoren kan ha slettet den, eller e-postadressen kan allerede være verifisert.';
                return;
            }
            $this->view->message = 'E-postadressen er verifisert. Du kan nå logge inn med e-postadressen og passordet du opprettet.';
            return;
        }

        $this->view->usr = $this->_getParam('usr', '');

        if ($this->getRequest()->isPost()) {

            if ($act == 'login') {
                $email    = $this->_getParam('email', '');
                $password = $this->_getParam('password', '');
                if (strpos($email, '@stud.ntnu.no') === false) $email .= '@stud.ntnu.no'; // In case people start just using their username (instead of full studmail) when logging in.
                try {
                    $user = Model_DbTable_Users::loginUser($email, $password, true);
                    return $this->_redirect($this->_getParam('ref', '/'));
                }catch(Exception $e) {
                    if ($e->getMessage() == 'No such user' || $e->getMessage() == 'Invalid password') {
                        $this->view->error = 'Brukernavn eller passord stemmer ikke';
                    }else if ($e->getMessage() == 'Email has not been verified yet') {
                        $this->view->error = 'Bruker har ikke verifisert e-posten sin enda, sjekk innboksen din (student epost).';
                    }else throw $e;
                }
            }else if ($act == 'register') {
                $username = trim($this->_getParam('username', false));
                $name     = trim($this->_getParam('name', false));
                $password = $this->_getParam('password', false);
                $passwordConfirm = $this->_getParam('passwordConfirm', false);

                if (!$username || !$name || $name == '' || !$password || $password == '' || !$passwordConfirm) {
                    $this->view->errorReg = 'Du må fylle inn en gyldig studentepostadresse, fult navn og passord to ganger.';
                    return;
                }

                if ($password != $passwordConfirm) {
                    $this->view->errorReg = 'Passordene var ikke like.';
                    return;
                }

                try {
                    $user = Model_DbTable_Users::createUser($username.'@stud.ntnu.no', $password, $name);
                    if (!$user) {
                        $this->view->errorReg = 'Noe gikk galt, men jeg vet ikke hva! Prøv igjen, eller kontakt websideansvarlig.';
                        return;
                    }
                    $this->view->message = 'Brukerkonto opprettet. Før du kan logge deg inn må du verifisere e-postadressen. Sjekk innboksen din og klikk på lenken som medfølger.';
                }catch (Exception $e) {
                    if ($e->getMessage() == 'There is no registration right for this email') {
                        $this->view->errorReg = 'Det er ikke registrert at denne studenteposten (' . $username.'@stud.ntnu.no) har rett til å registrere seg. Om du er medlem av Mannhullet linjeforening, ta kontakt med webansvarlig.';
                    }else throw $e;
                }
                return;
            }else if ($act == 'passwordreset') {
                $studmail = trim($this->_getParam('email', ''));
                if (strpos($studmail, '@stud.ntnu.no') === false) $studmail .= '@stud.ntnu.no'; // In case just username (not full email)
                try {
                    $user = Model_DbTable_Users::getUser($studmail);
                    if (!$user || $user->verificationCode != null || $user->created == null) {
                        $this->view->errorForgottenPw = 'Brukeren ble ikke funnet, eller e-postadressen er ikke verifisert enda. Har du skrevet korrekt studentepost?';
                        return;
                    }
                    if (!$user->resetPassword()) {
                        $this->view->errorforgottenpw = 'noe gikk galt med utsendelsen av nytt passord. prøv igjen senere eller kontakt webansvarlig.';
                        return;
                    }
                    $this->view->message = 'Passordet er nullstilt. Sjekk innboksen din for det nye passordet.';
                }catch (Exception $e) {
                    if ($e->getMessage() == 'No such user') {
                        $this->view->errorForgottenPw = 'Brukeren ble ikke funnet. Har du skrevet korrekt studentepost?';
                    }else throw $e;
                }
                return;
            }
        }
    }

    public function bilderAction()
    {
        if (!$this->user) throw new Exception('Not logged in');

        $this->view->mainnav = '/memberarea';
        $this->view->secondarynav = '/bilder';
        $this->view->headTitle('Bildegalleri');

        $aid = $this->_getParam('aid', -1);
        if ($aid < 0) {

            $albums = $this->view->albums = Model_DbTable_FileCollections::getAlbums();

            if (!$this->user->admin) return;

            if ($this->getRequest()->isPost()) {
            }

        }else{

            $this->view->lightbox = true;
            $album = $this->view->album = Model_DbTable_FileCollections::getAlbum($aid);

            $this->view->headTitle($album->category, 'PREPEND');
            $this->view->headTitle($album->title, 'PREPEND');

            if (!$this->user->admin) return;

            $this->view->uploadWidget = true;
            $this->view->uploadWidgetDestination = '/bilder/' . $album->id . '/act/uploader';
            $this->view->uploadWidgetFiletypes = array(
                'images' => true,
            );

            $act = $this->_getParam('act', '');
            if ($act == 'deleteimage') {
                $imageId = $this->_getParam('iid', '');
                $image = Model_DbTable_Files::getFileById($imageId);
                $album->removeFile($image);
                return $this->_redirect('/bilder/' . $album->id);
            }else if ($act == 'useascover') {
                $imageId = $this->_getParam('iid', '');
                $image = Model_DbTable_Files::getFileById($imageId);
                $album->cover_image_src = $image->src;
                $album->save();
                return $this->_redirect('/bilder/' . $album->id);
            }

            if ($this->getRequest()->isPost()) {

                $act = $this->_getParam('act', false);
                if ($act == 'uploader') {
                    // Will terminate the php execution when complete
                    Model_Uploader::handleRequestAlbum($album);
                }

            }

        }
    }

    public function marinaAction()
    {
        if (!$this->user) throw new Exception('Not logged in');

        $this->view->mainnav = '/memberarea';
        $this->view->secondarynav = '/marina';
        $this->view->headTitle('Marina');

        $mid = $this->_getParam('mid', -1);
        if ($mid < 0) {

            $marinas = $this->view->marinas = Model_DbTable_FileCollections::getMarinas();

            if (!$this->user->admin) return;

            $this->view->uploadWidget = true;
            $this->view->uploadWidgetDestination = '/marina';

            if ($this->getRequest()->isPost()) {

                $act = $this->_getParam('act', '');
                if ($act == 'editMarina') {
                    $mid = $this->_getParam('maid', -1);
                    $image = $this->_getParam('image', '');
                    $marina = Model_DbTable_FileCollections::getCollection($mid);
                    $marina->cover_image_src = $image;
                    $marina->save();
                    return $this->_redirect('/marina');
                }

                $year = $this->_getParam('year', false);
                Model_Uploader::handleRequestMarina($year);

            }

        }else{

            $act = $this->_getParam('act', '');
            if ($act == 'deletemarina') {

                if (!$this->user->admin) return;

                $marina = Model_DbTable_FileCollections::getCollection($mid);
                $filename = APPLICATION_PATH . '/../public/' . $marina->src;
                if (is_file($filename)) @unlink($filename);
                $marina->delete();
                return $this->_redirect('/marina');

            }else{

                Model_DbTable_FileCollections::downloadCollectionFile($mid);

            }

        }
    }

    public function dokumenterAction()
    {
        if (!$this->user) throw new Exception('Not logged in');

        $this->view->mainnav = '/memberarea';
        $this->view->secondarynav = '/nths';
        $this->view->headTitle('Dokumenter');

        $nid = $this->_getParam('nid', -1);
        if ($nid < 0) {

            $nthsdocs = $this->view->nthsdocs = Model_DbTable_FileCollections::getNTHSDocs();

            if (!$this->user->admin) return;

            $this->view->uploadWidget = true;
            $this->view->uploadWidgetDestination = '/dokumenter';

            if ($this->getRequest()->isPost()) {

                $year = $this->_getParam('year', false);
                Model_Uploader::handleRequestNTHS($year);

            }

        }else{

            $act = $this->_getParam('act', '');
            if ($act == 'deletedocument') {

                if (!$this->user->admin) return;

                $nths = Model_DbTable_FileCollections::getCollection($nid);
                $filename = APPLICATION_PATH . '/../public/' . $nths->src;
                if (is_file($filename)) @unlink($filename);
                $nths->delete();
                return $this->_redirect('/dokumenter');

            }else{

                Model_DbTable_FileCollections::downloadCollectionFile($nid);

            }
        }
    }

    public function innstillingerAction()
    {
        if (!$this->user) return $this->_redirect('/');

        if ($this->getRequest()->isPost()) {
            $password = $this->_getParam('password', '');
            if (!$this->user->checkPassword($password)) {
                $this->view->error = 'Ditt nåværende passord stemte ikke overens med innfylt nåværende passord.';
                return;
            }
            $newPassword = $this->_getParam('newPassword', '');
            if ($newPassword == '') {
                $this->view->error = 'Passord er obligatorisk, du må fylle inn noe du husker. Prøv igjen.';
                return;
            }
            $newPasswordConfirm = $this->_getParam('newPasswordConfirm', '');
            if ($newPassword != $newPasswordConfirm) {
                $this->view->error = 'Det nye passordet ble ikke skrevet likt i begge feltene! Prøv igjen.';
                return;
            }
            $this->user->reInitiate()->setPassword($newPassword);
            $this->view->error = 'Nytt passord satt.';
            return;
        }
    }

    public function loggutAction()
    {
        Model_DbTable_Users::logoutUser();
        return $this->_redirect('/');
    }

    public function pluginAction()
    {
        if (!$this->getRequest()->isPost()) throw new Exception('Only accepts post-requests from plugin-forms');
        $plugin = $this->_getParam('plugin', '');
        $result = Model_Plugins::handlePost($plugin, $_POST);
        return $this->_redirect($_SERVER['HTTP_REFERER']);
    }
}
