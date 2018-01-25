<?php

require_once(APPLICATION_PATH . '/../library/google-api-php-client/src/Google/autoload.php');

class Model_Plugins
{
    public static function filter($content)
    {
        $plugins = new self();

        $pattern = '/\{plugin ([a-z]+)\}/';
        if (preg_match_all($pattern, $content, $matches) > 0) {
            foreach ($matches[1] as $plugin) {

                $pluginMethod = 'plugin' . ucfirst($plugin);
                if (method_exists($plugins, $pluginMethod)) {
                    $html = $plugins->$pluginMethod();
                    $content = str_replace("{plugin $plugin}", $html, $content);
                }

            }
        }

        return $content;
    }

    public static function handlePost($plugin, $params)
    {
        $plugins = new self();
        $pluginMethod = 'plugin' . ucfirst($plugin) . 'HandlePost';
        if (method_exists($plugins, $pluginMethod)) {
            return $plugins->$pluginMethod($params);
        }else{
            throw new Exception('No plugin with name ' . $plugin);
        }
    }

    public function pluginMbkreservasjonssystem()
    {
        // Check if we're logged in or not
        $userSession = new Zend_Session_Namespace('user');
        $user = (isset($userSession->user) ? $userSession->user : false);

        ob_start();
?>
        <!-- Plugin Mbkreservasjonssystem Start -->
        <div style="margin:30px 0 30px 0;" class="panel panel-success">
            <div class="panel-heading">Opprett reservasjon</div>
            <div class="panel-body">
<?php if (!$user): ?>
                <div class="alert alert-warning">
                    Man må være innlogget for å reservere båt.
                </div>
<?php else: ?>
                <form action="/index/plugin" method="POST" class="form-horizontal" role="form">
                    <input type="hidden" name="plugin" value="mbkreservasjonssystem" />
                    <div class="form-group">
                        <label for="email" class="col-sm-2 control-label">Ansvarlig</label>
                        <div class="col-sm-10">
                            <input type="text" name="emailshow" value="<?php echo $user->email; ?>" class="form-control" />
                            <input type="hidden" name="email" value="<?php echo $user->email; ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="certificate" class="col-sm-2 control-label">Sertifikatnr.</label>
                        <div class="col-sm-10">
                            <input type="text" name="certificate" class="form-control" placeholder="Sertifikatnummer" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start_date" class="col-sm-2 control-label">Start- / sluttdato</label>
                        <div class="col-sm-10">
                            <div id="datepicker-mbkreservasjonssystem" class="input-daterange input-group">
                                <input type="text" name="start_date" class="form-control" />
                                <span class="input-group-addon">til</span>
                                <input type="text" name="end_date" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start_time" class="col-sm-2 control-label">Start- / sluttidspunkt <small>(tt:mm)</small></label>
                        <div class="col-sm-10">
                            <div class="input-daterange input-group">
                                <input type="time" name="start_time" class="form-control" placeholder="tt:mm" />
                                <span class="input-group-addon">til</span>
                                <input type="time" name="end_time" class="form-control" placeholder="tt:mm" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Opprett reservasjon nå</button>
                        </div>
                    </div>
                </form>
<?php endif; ?>
            </div>
        </div>
        <!-- Plugin Mbkreservasjonssystem End -->
<?php
        $html = ob_get_clean();
        return $html;
    }

    public function pluginMbkreservasjonssystemHandlePost($params)
    {
        if (!Model_DbTable_PluginMBKReservations::verifyCertificateAndEmail($params['certificate'], $params['email'])) {
            return false; // No match in the database
        }


        $certificate_number = $params['certificate'];
        $email = $params['email'];
        $name = Model_DbTable_PluginMBKReservations::getCertificate($certificate_number)->getUser()->name;

        $date_from = $params['start_date'];
        $date_to   = $params['end_date'];
        $time_from = $params['start_time'];
        $time_to   = $params['end_time'];

        $messageText =  <<<EOF
Kvittering for reservasjon av Havfruen V

Personalia:
Navn: $name
E-post: $email
Sertifikatnummer: $certificate_number

Tidspunkt:
Fra dato: $date_from
Til dato: $date_to
Fra klokken: $time_from
Til klokken: $time_to

For å kunne få utlevert nøkkel må utskrift av denne e-post medbringes og leveres på biblioteket på MTS, sammen med ditt Havfruen-bevis.

Dersom du har spørsmål om din reservasjon, kontakt MBK-Skipper Simon Bjørneset på telefon 95 94 92 78, eller via mail til simob@stud.ntnu.no.

Har du problemer med båten ta kontakt med Førsestyrmann Jenny Wingyen Leung på telefon 98 65 32 29, eller via mail til jennywl@stud.ntnu.no.

Hand this e-mail (you need to print it out) with your havfruen-certificate in the library at MTS to get the keys.

If you have any questions about your reservation, contact MBK Skipper Simon Bjørneset on 95 94 92 78, or by mail at simob@stud.ntnu.no.

If you have any questions regarding the usage of the boat, contact First mate Jenny Wingyen Leung on 98 65 32 29, or by mail at jennywl@stud.ntnu.no.

Mvh
Motorbåtkomiteen
EOF;

        $mail = new Zend_Mail('utf-8');
        $mail->setMessageId();
        $mail->addTo($email);
        $mail->setSubject('Kvittering for reservasjon av Havfruen IV');
        $mail->setFrom('no-reply@mannhullet.no','Mannhullet.no');
        $mail->setBodyText($messageText);
        $mail->send();


        $calendarId = 'am09tn7f45n6u0cbksppblc2qc@group.calendar.google.com';
        $service_account_name = "175549401939-jhum7qd8vl96cm05i4a6s7kh18iiifgr@developer.gserviceaccount.com";
        $client_id = "175549401939-jhum7qd8vl96cm05i4a6s7kh18iiifgr.apps.googleusercontent.com";
        $key = "-----BEGIN PRIVATE KEY-----\nMIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBALeaGDA5as1SIAT4\njWNURf84Kr2pHNU5T7yOI1msNznTLHljJScYmtf40FTozhF3t0FJCjCIvHGBCeJz\n97U/WKVg9+tVGrYR/IQg8mB5ElB9rmPgptFWH4InznGDSA/aXXSgg9ZtS2+DynHH\n8JSBVx/kVOSzRDS2/Ba2m5xHdm8fAgMBAAECgYA2LZ+To23TtrdCIEJAnF6naGCc\nZOngNbBE2MCvtnT5eEo4a7xL5CPVNVPsqmIcn3IRLsd1+PN6nvRWwZfIATBb5DPs\n2nbJE29oiJrefPnygEK5V4l7gm4YZb1oggDSjYHUmKmLubKs2a1xN7iPVKrLjqS/\nxaaHFNVydn1YAYKxeQJBAPCLvqJ/Ih++JkeW5n3Zfs+bW8Ekz5+wOiQKZxfdJD+6\npvSai17sD+2nHOJCYGuZFnQI0aeqabLh+H7fI4lulbUCQQDDZcsbDm67lF78v8M2\nlhxSz+IWmfCBSpbYoJnDVjWjE48eKYr6D5jSHP4EbyOq3HDAZKoG5cDbqu6uxTcP\nTLYDAkAZNjxr4bFc7Fwswrcz15j//4OVcdtFHH5rip+Vk7sZ5uFa39vdvhZJTWus\nl1Jt1KTS0p3O2gCsHB0khxS9cdbFAkAy4c0UDJwVSLu7gYwqKMjTX8L2M7wHTw8c\n9iVUUpzBDJTWO+cu2uTmwhn7uZ1GHwVVdGE6TpX8HfTtmfmOiGFVAkBgFRKMuZ9M\nX9W13m1dDfzobiJEx5hZzzWsK6b7b/2cu7LB4WuPFBsdMUVn79II3WQVd2cgDwwn\nMThyjL2T3fSW\n-----END PRIVATE KEY-----\n";

        $client = new Google_Client();
        $client->setApplicationName('Mannhullet');
        $client->setClientId($client_id);

        $cred = new Google_Auth_AssertionCredentials(
            $service_account_name,
            array('https://www.googleapis.com/auth/calendar'),
            $key
        );
        $client->setAssertionCredentials($cred);

        // Get / refresh credentials
        if($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event();
        $event->setSummary('Opptatt');
        $event->setLocation('Båt reservert av ' . $params['email'] . ', sertifikatnummer ' . $params['certificate']);

        // Set the date using RFC 3339 format.
        $startDate = $params['start_date'];
        $startTime = $params['start_time'];
        $endDate = $params['end_date'];
        $endTime = $params['end_time'];
        $tzOffset = "+0" . (1+date('I'));

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime("{$startDate}T{$startTime}:00.000{$tzOffset}:00");
        $event->setStart($start);

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime("{$endDate}T{$endTime}:00.000{$tzOffset}:00");
        $event->setEnd($end);

        $createdEvent = $service->events->insert($calendarId, $event);

        return true;
    }
}
