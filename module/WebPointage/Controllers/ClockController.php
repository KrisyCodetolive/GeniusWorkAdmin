<?php
/*
* Genius Work - A time clock application for employees
* URL: https://www.gwork.genius.ci
* Support: info@geniusgroups.ci
* Version: 6.5
* Author: Genius Groups
* Copyright 2023 Genius Groups
*/
namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Classes\Table;
use App\Classes\Permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\VoiceRSS;

class ClockController extends Controller
{
    public function getuser(Request $request){
        $idno = strtoupper($request->idno);
        $employee_id = table::companydata()->where('idno', $idno)->value('reference');
        if($employee_id == null) 
        {
            return response()->json([
                "error" => trans("Employee not found")
            ]);
        }
        $employee = table::companydata()->where('idno', $idno)->first();
        $person = table::people()->where('id', $employee_id)->first();

        $tablegreat = [
            'Bonjour', 'Hello', 'Comment vous allez ?'
        ];
        $tablegreatkey = array_rand($tablegreat);
        $greatrand = $tablegreat[$tablegreatkey];

        $tablewish = ['Bonne Journée', 'Bonne Arrivée leader', 'Bon travail', 'Souriez la vie est belle'];

        $tablewishkey = array_rand($tablewish);
        $wish = $tablewish[$tablewishkey];
        

        $great =  $greatrand . ' ' . $person->lastname . ' ' . $person->firstname . ' '  . $wish;

        
        $tts = new VoiceRSS;

        $voice = $tts->speech([
            'key' => '75a83fed9f3e4acda616ce5fdea5c8fa',
            'hl' => 'fr-FR',
            'v' => 'Linda',
            'src' =>  $great,
            'r' => '-1',
            'c' => 'mp3',
            'f' => '44khz_16bit_stereo',
            'ssml' => 'false',
            'b64' => 'true'
        ]);

//        dd($voice);

        return view('info', compact('employee', 'person', 'voice', 'idno'));
    }


    
    public function index()
    {
        $data = table::settings()->where('id', 1)->first();
        
        $timezone = $data->timezone;
        
        $timeformat = $data->time_format;
        
        $rfid = $data->rfid;
        
        return view('webclock', [
            'timezone' => $timezone, 
            'timeformat' => $timeformat, 
            'rfid' => $rfid
        ]);
    }

    public function smartClock()
    {
        $data = table::settings()->where('id', 1)->first();
        return view('smart-clock', [
            'timezone' => $data->timezone,
            'timeformat' => $data->time_format
        ]);
    }

    public function clocking(Request $request)
    {
        if ($request->idno == null) 
        {
            return response()->json([
                "error" => trans("Please enter your ID number")
            ]);
        }
    
        if($request->type ==  null) 
        {
            return response()->json([
                "error" => trans("Please click the click-in or clock-out button")
            ]);
        }
    
        $idno = strtoupper($request->idno);
        $type = $request->type;
        $date = date('Y-m-d');
        $time = date('h:i:s A');
        $ip = $request->ip();
    
        # ip restriction
        $iprestriction = table::settings()->value('iprestriction');
        if ($iprestriction != null) 
        {
            $ips = explode(",", $iprestriction);
            if(in_array($ip, $ips) == false) 
            {
                return response()->json([
                    "error" => trans("Your device is not registered")
                ]);
            }
        } 
    
        # employee
        $employee_id = table::companydata()->where('idno', $idno)->value('reference');
        if($employee_id == null) 
        {
            return response()->json([
                "error" => trans("Employee not found")
            ]);
        }
    
        $person = table::people()->where('id', $employee_id)->first();
        $lastname = $person->lastname;
        $firstname = $person->firstname;
        $employee = mb_strtoupper($lastname.', '.$firstname);
    
        # settings 
        $settings = table::settings()->where('id', 1)->first();
        $timezone = $settings->timezone;
        $timeformat = $settings->time_format;
    
        if ($type == 'clockin') 
        {
            $has = table::attendance()->where([['idno', $idno],['date', $date]])->exists();
            if ($has == 1) 
            {
                $hti = table::attendance()->where([['idno', $idno],['date', $date]])->value('timein');
                $hti = date('h:i A', strtotime($hti));
                $hti_24 = ($timeformat == 12) ? $hti : date("H:i", strtotime($hti));
                return response()->json([
                    "error" => trans("You were clocked-in today at")." ".$hti_24,
                ]);
            } else {
                $last_in_notimeout = table::attendance()->where([['idno', $idno],['timeout', NULL]])->count();
                if($last_in_notimeout >= 1)
                {
                    return response()->json([
                        "error" => trans("You are not allowed to clock in twice or more in a day")
                    ]);
                } else {
                    $sched_in_time = table::schedules()->where([['idno', $idno], ['archive', 0]])->value('intime');
                    if($sched_in_time == NULL)
                    {
                        $status_in = null;
                    } else {
                        $sched_clock_in_time_24h = date("H.i", strtotime($sched_in_time));
                        $time_in_24h = date("H.i", strtotime($time));
                        if ($time_in_24h <= $sched_clock_in_time_24h) 
                        {
                            $status_in = trans("In Time");
                        } else {
                            $status_in = trans("Late In");
                        }
                    }
    
                    table::attendance()->insert([
                        [
                            'idno' => $idno,
                            'reference' => $employee_id,
                            'date' => $date,
                            'employee' => $employee,
                            'timein' => $date." ".$time,
                            'status_timein' => $status_in,
                        ],
                    ]);
    
                    $great = $this->generateWelcomeMessage($person);
    
                    $tts = new VoiceRSS;
                    $voice = $tts->speech([
                        'key' => '75a83fed9f3e4acda616ce5fdea5c8fa',
                        'hl' => 'fr-FR',
                        'v' => 'Linda',
                        'src' =>  $great,
                        'r' => '-1',
                        'c' => 'mp3',
                        'f' => '44khz_16bit_stereo',
                        'ssml' => 'false',
                        'b64' => 'true'
                    ]);
    
                    return response()->json([
                        "type" => $type,
                        "time" => $time,
                        "date" => $date,
                        "employee" => $employee,
                        "voice" => $voice['response'],
                    ]);
                }
            }
        }
        
        if ($type == 'clockout') 
        {
            $timeIN = table::attendance()->where([['idno', $idno], ['timeout', NULL]])->value('timein');
            $clockInDate = table::attendance()->where([['idno', $idno],['timeout', NULL]])->value('date');
            $hasout = table::attendance()->where([['idno', $idno],['date', $date]])->value('timeout');
            $timeOUT = date("Y-m-d h:i:s A", strtotime($date." ".$time));
            if($timeIN == NULL) 
            {
                return response()->json([
                    "error" => trans("You are not clocked-in")
                ]);
            } 
            if ($hasout != NULL) 
            {
                $hto = table::attendance()->where([['idno', $idno],['date', $date]])->value('timeout');
                $hto = date('h:i A', strtotime($hto));
                $hto_24 = ($timeformat == 12) ? $hto : date("H:i", strtotime($hto));
                return response()->json([
                    "error" => trans("You were clocked-out today at")." ".$hto_24,
                ]);
            } else {
                $sched_out_time = table::schedules()->where([['idno', $idno], ['archive', 0]])->value('outime');
                if($sched_out_time == NULL) 
                {
                    $status_out = null;
                } else {
                    $sched_clock_out_time_24h = date("H.i", strtotime($sched_out_time));
                    $time_out_24h = date("H.i", strtotime($timeOUT));
                    if($time_out_24h >= $sched_clock_out_time_24h) 
                    {
                        $status_out = trans("On Time"); 
                    } else {
                        $status_out = trans("Early Out"); 
                    }
                }
    
                $time1 = Carbon::createFromFormat("Y-m-d h:i:s A", $timeIN); 
                $time2 = Carbon::createFromFormat("Y-m-d h:i:s A", $timeOUT); 
                
                // Calculer la différence en minutes
                $diffInMinutes = $time1->diffInMinutes($time2);
                
                // Convertir les heures précédentes en minutes
                $previousTotalHours = floatval($lastRecord->totalhours ?? 0);
                $prevHours = floor($previousTotalHours);
                $prevMinutes = round(($previousTotalHours - $prevHours) * 100);
                $previousTotalMinutes = ($prevHours * 60) + $prevMinutes;
                
                // Ajouter les minutes actuelles aux minutes précédentes
                $totalMinutes = $previousTotalMinutes + $diffInMinutes;
                
                // Reconvertir en format heures.minutes
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $totalhour = $hours . '.' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

                table::attendance()->where([['idno', $idno],['date', $clockInDate]])->update(array(
                    'timeout' => $timeOUT,
                    'totalhours' => $totalhour,
                    'status_timeout' => $status_out)
                );
    
                $great = $this->generateWelcomeMessage($person);
    
                $tts = new VoiceRSS;
                $voice = $tts->speech([
                    'key' => '75a83fed9f3e4acda616ce5fdea5c8fa',
                    'hl' => 'fr-FR',
                    'v' => 'Linda',
                    'src' =>  $great,
                    'r' => '-1',
                    'c' => 'mp3',
                    'f' => '44khz_16bit_stereo',
                    'ssml' => 'false',
                    'b64' => 'true'
                ]);
    
                return response()->json([
                    "type" => $type,
                    "time" => $time,
                    "date" => $date,
                    "employee" => $employee,
                    "voice" => $voice['response'],
                ]);
            }
        }
    }
    
    public function autoClocking(Request $request)
    {
        if ($request->idno == null) {
            return response()->json([
                "error" => trans("QR Code invalide ou non reconnu")
            ]);
        }

        $idno = strtoupper($request->idno);
        $date = date('Y-m-d');
        $time = date('h:i:s A');
        $ip = $request->ip();

        # ip restriction
        $iprestriction = table::settings()->value('iprestriction');
        if ($iprestriction != null) {
            $ips = explode(",", $iprestriction);
            if(in_array($ip, $ips) == false) {
                return response()->json([
                    "error" => trans("Dispositif non autorisé")
                ]);
            }
        }

        # employee
        $employee_id = table::companydata()->where('idno', $idno)->value('reference');
        if($employee_id == null) {
            return response()->json([
                "error" => trans("Employé non trouvé")
            ]);
        }

        $person = table::people()->where('id', $employee_id)->first();
        $lastname = $person->lastname;
        $firstname = $person->firstname;
        $employee = mb_strtoupper($lastname.', '.$firstname);

        # settings
        $settings = table::settings()->where('id', 1)->first();
        $timezone = $settings->timezone;
        $timeformat = $settings->time_format;

        # Vérifier les pointages non terminés de la veille
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $incompleteAttendance = table::attendance()
            ->where('idno', $idno)
            ->whereDate('date', $yesterday)
            ->whereNull('timeout')
            ->first();

        if ($incompleteAttendance) {
            // Mise à jour du pointage incomplet de la veille
            table::attendance()
                ->where('id', $incompleteAttendance->id)
                ->update([
                    'timeout' => $yesterday . ' 23:59:59',
                    'totalhours' => '0',
                    'reason' => 'Negligeance',
                    'comment' => 'Journée de travail non prise en compte',
                    'status_timeout' => 'Late Out'
                ]);
        }

        # Vérifier le dernier enregistrement de la journée
        $lastRecord = table::attendance()
            ->where('idno', $idno)
            ->where('date', $date)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastRecord) {
            // Premier pointage de la journée (Clock In)
            $sched_in_time = table::schedules()->where([['idno', $idno], ['archive', 0]])->value('intime');
            $status_in = null;
            
            if($sched_in_time != NULL) {
                $sched_clock_in_time_24h = date("H.i", strtotime($sched_in_time));
                $time_in_24h = date("H.i", strtotime($time));
                $status_in = ($time_in_24h <= $sched_clock_in_time_24h) ? trans("In Time") : trans("Late In");
            }

            table::attendance()->insert([
                'idno' => $idno,
                'reference' => $employee_id,
                'date' => $date,
                'employee' => $employee,
                'timein' => $date." ".$time,
                'status_timein' => $status_in,
            ]);

            $action = "clockin";
        } 
        elseif ($lastRecord->timeout === null) {
            // Deuxième pointage (Clock Out)
            $timeIN = $lastRecord->timein;
            $timeOUT = date("Y-m-d h:i:s A", strtotime($date." ".$time));
            
            $sched_out_time = table::schedules()->where([['idno', $idno], ['archive', 0]])->value('outime');
            $status_out = null;
            
            if($sched_out_time != NULL) {
                $sched_clock_out_time_24h = date("H.i", strtotime($sched_out_time));
                $time_out_24h = date("H.i", strtotime($timeOUT));
                $status_out = ($time_out_24h >= $sched_clock_out_time_24h) ? trans("On Time") : trans("Early Out");
            }

            $time1 = Carbon::createFromFormat("Y-m-d h:i:s A", $timeIN); 
            $time2 = Carbon::createFromFormat("Y-m-d h:i:s A", $timeOUT); 
            
            // Calculer la différence en minutes
            $diffInMinutes = $time1->diffInMinutes($time2);
            
            // Convertir les heures précédentes en minutes
            $previousTotalHours = floatval($lastRecord->totalhours ?? 0);
            $prevHours = floor($previousTotalHours);
            $prevMinutes = round(($previousTotalHours - $prevHours) * 100);
            $previousTotalMinutes = ($prevHours * 60) + $prevMinutes;
            
            // Ajouter les minutes actuelles aux minutes précédentes
            $totalMinutes = $previousTotalMinutes + $diffInMinutes;
            
            // Reconvertir en format heures.minutes
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $totalhour = $hours . '.' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

            table::attendance()->where('id', $lastRecord->id)->update([
                'timeout' => $timeOUT,
                'totalhours' => $totalhour,
                'status_timeout' => $status_out
            ]);

            $action = "clockout";
        } 
        else {
            // Troisième pointage et plus (Mise à jour du dernier enregistrement)
            $previousTotalHours = floatval($lastRecord->totalhours ?? 0);
            
            // Mettre à jour le dernier enregistrement avec un nouveau time in
            table::attendance()->where('id', $lastRecord->id)->update([
                'timein' => $date." ".$time,
                'timeout' => null,
                'status_timein' => trans("Return In"),
                'totalhours' => $previousTotalHours,
                'status_timeout' => null
            ]);

            $action = "return_clockin";
        }

        // Message de bienvenue personnalisé
        $great = $this->generateWelcomeMessage($person);
        
        // Synthèse vocale
        $tts = new VoiceRSS;
        $voice = $tts->speech([
            'key' => '75a83fed9f3e4acda616ce5fdea5c8fa',
            'hl' => 'fr-FR',
            'v' => 'Linda',
            'src' =>  $great,
            'r' => '-1',
            'c' => 'mp3',
            'f' => '44khz_16bit_stereo',
            'ssml' => 'false',
            'b64' => 'true'
        ]);

        return response()->json([
            "type" => $action,
            "time" => $time,
            "date" => $date,
            "employee" => $employee,
            "voice" => $voice['response'],
        ]);
    }
    
    public function getRecentLogs()
    {
        try {
            $recentLogs = table::attendance()
                ->select(
                    'people_attendance.id',
                    'people_attendance.timein',
                    'people_attendance.timeout',
                    'people.firstname',
                    'people.lastname'
                )
                ->from('people_attendance')
                ->join('people', 'people_attendance.reference', '=', 'people.id')
                ->orderBy('people_attendance.timein', 'desc')
                ->take(5)
                ->get();

            $formattedLogs = $recentLogs->map(function ($log) {
                try {
                    $timeIn = Carbon::parse($log->timein);
                    $timeOut = $log->timeout ? Carbon::parse($log->timeout) : null;
                    $latestTime = $timeOut ?? $timeIn;
                    
                    // S'assurer que firstname et lastname ne sont pas null
                    $firstname = $log->firstname ?? '';
                    $lastname = $log->lastname ?? '';
                    
                    return [
                        'id' => $log->id,
                        'name' => trim($firstname . ' ' . $lastname),
                        'timeAgo' => $latestTime->diffForHumans(),
                        'type' => $timeOut ? 'Sortie' : 'Entrée',
                        'initials' => strtoupper(
                            substr($firstname, 0, 1) . 
                            substr($lastname, 0, 1)
                        )
                    ];
                } catch (\Exception $e) {
                    \Log::error('Erreur lors du formatage d\'un log: ' . $e->getMessage());
                    return null;
                }
            })
            ->filter() // Retire les entrées null
            ->values(); // Réindexe le tableau

            return response()->json($formattedLogs);

        } catch (\Exception $e) {
            \Log::error('Erreur dans getRecentLogs: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de la récupération des pointages',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    public function generateWelcomeMessage($person)
    {
        // Contexte temporel
        $currentHour = (int)date('H');
        $currentMinute = (int)date('i');
        $currentDay = date('l');
        $currentMonth = date('F');
        $currentDate = date('d');
        $isHoliday = $this->isHoliday();
        $isWeekend = in_array($currentDay, ['Saturday', 'Sunday']);
        
        // Contexte de l'employé
        $firstName = ucfirst(strtolower($person->firstname));
        $lastName = ucfirst(strtolower($person->lastname));
        $jobTitle = strtoupper($person->jobtitle ?? '');
        $isManager = str_contains($jobTitle, 'MANAGER') || str_contains($jobTitle, 'CEO') || str_contains($jobTitle, 'DSI');
        
        // Salutations adaptatives selon l'heure
        $greetings = match(true) {
            $currentHour < 6 => ['Très tôt au bureau', 'Quelle motivation matinale'],
            $currentHour < 9 => ['Bonjour', 'Bon début de journée', 'Belle matinée'],
            $currentHour < 12 => ['Bonne matinée', 'Hello', 'Bonjour'],
            $currentHour < 14 => ['Bon midi', 'Bonjour'],
            $currentHour < 17 => ['Bon après-midi', 'Hello'],
            $currentHour < 20 => ['Bonne soirée', 'Bonsoir'],
            default => ['Vous travaillez tard', 'Bonne soirée']
        };
        
        // Messages contextuels selon l'heure
        $timeContextMessages = match(true) {
            $currentHour < 6 => "C'est très tôt, prenez soin de vous!",
            $currentHour === 8 && $currentMinute <= 30 => "Pile à l'heure, belle ponctualité!",
            $currentHour >= 20 => "N'oubliez pas de vous reposer!",
            default => null
        };
        
        // Messages spéciaux pour chaque poste
        $roleMessages = [
            'CEO' => [
                'Votre leadership inspire l\'équipe',
                'Prêt pour de nouveaux défis?',
                'L\'équipe est motivée sous votre direction',
                'Votre vision guide l\'entreprise vers le succès'
            ],
            'DSI' => [
                'Les systèmes sont sous contrôle',
                'Innovation et sécurité, nos priorités',
                'Votre expertise technique guide l\'équipe',
                'Maintenons notre excellence technologique'
            ],
            'ASSISTANTE AU CEO' => [
                'Votre support est précieux pour l\'équipe',
                'Excellence et organisation, vos maîtres-mots',
                'Merci de coordonner nos activités avec brio',
                'Votre efficacité fait la différence'
            ],
            'PROJECT MANAGER' => [
                'Les projets avancent grâce à vous',
                'Une nouvelle journée de défis passionnants',
                'Votre coordination est essentielle',
                'Ensemble vers nos objectifs'
            ],
            'ATTACHE ADMINISTRATIF' => [
                'Votre rigueur fait notre force',
                'L\'administration en de bonnes mains',
                'Merci pour votre organisation exemplaire',
                'Votre précision est appréciée'
            ],
            'OPERATION MANAGER' => [
                'Les opérations sont entre de bonnes mains',
                'Optimisons nos processus ensemble',
                'Votre supervision est précieuse',
                'Excellence opérationnelle en action'
            ],
            'TEAM IT MANAGER' => [
                'L\'innovation IT continue avec vous',
                'Guidez l\'équipe vers l\'excellence technique',
                'Votre expertise fait grandir l\'équipe',
                'Ensemble vers de nouvelles solutions'
            ],
            'COMMUNICATION MANAGER' => [
                'Portons notre message avec impact',
                'Votre créativité inspire l\'équipe',
                'Une nouvelle journée de communication efficace',
                'Donnons de la voix à nos projets'
            ],
            'COMMUNITY MANAGER' => [
                'Créons du lien avec notre communauté',
                'Votre engagement fait la différence',
                'Une journée d\'interactions enrichissantes',
                'Rayonnons sur les réseaux'
            ],
            'MARKETING MANAGER' => [
                'Innovons dans nos stratégies',
                'Votre vision marketing nous distingue',
                'Développons notre impact ensemble',
                'Créativité et résultats au rendez-vous'
            ],
            'AMBASSADEUR GENIUS' => [
                'Représentez nos valeurs avec fierté',
                'Votre enthousiasme est contagieux',
                'Une journée pour briller',
                'Inspirez l\'excellence autour de vous'
            ],
            'COMMERCIALE MANAGER' => [
                'Que les objectifs soient avec vous',
                'Une journée de nouvelles opportunités',
                'Votre dynamisme fait la différence',
                'Ensemble vers de nouveaux succès'
            ],
            'CUSTOMER MANAGER' => [
                'La satisfaction client est notre priorité',
                'Une journée d\'excellence relationnelle',
                'Votre écoute fait la différence',
                'Créons des expériences mémorables'
            ],
            'STAGIAIRE DEVELOPPEUR' => [
                'Une journée riche en apprentissages',
                'Votre progression est encourageante',
                'Codons l\'avenir ensemble',
                'Chaque défi est une opportunité'
            ]
        ];
        
        // Messages pour chaque jour de la semaine
        $dayMessages = [
            'Monday' => [
                'Je vous souhaite une excellente semaine', 'Je vous souhaite Une nouvelle semaine pleine d\'opportunités',
                'Démarrons cette semaine avec énergie',
                $currentHour < 10 ? 'En forme pour la semaine?' : 'La semaine est bien lancée',
                'Nouveau lundi, nouveaux objectifs'
            ],
            'Tuesday' => [
                'La semaine est bien engagée',
                'Gardons notre dynamique positive',
                'Une journée pour concrétiser nos projets',
                $currentHour < 12 ? 'La journée est prometteuse' : 'Continuons sur notre lancée'
            ],
            'Wednesday' => [
                'À mi-chemin de nos objectifs',
                'Le milieu de semaine est prometteur',
                'Gardons le cap vers le succès',
                $currentHour < 12 ? 'La semaine avance bien' : 'Plus que deux jours'
            ],
            'Thursday' => [
                'L\'énergie est au rendez-vous',
                'Bientôt le weekend, restons motivés',
                'Une journée pour finaliser nos projets',
                $currentHour < 12 ? 'Encore une belle journée' : 'Le weekend approche'
            ],
            'Friday' => [
                $currentHour < 12 ? 'Dernière ligne droite avant le weekend' : 'Le weekend approche',
                'Finissons la semaine en beauté',
                'Bientôt le repos bien mérité',
                'Une semaine de plus vers nos objectifs'
            ],
            'Saturday' => [
                'Merci de votre engagement le weekend',
                'Votre présence fait la différence',
                $currentHour < 12 ? 'Bon courage pour ce samedi' : 'Profitez de votre soirée',
                'Un samedi productif en perspective'
            ],
            'Sunday' => [
                'Votre dévouement est remarquable',
                'Un dimanche au service de l\'excellence',
                $currentHour < 12 ? 'Bon dimanche au bureau' : 'Bonne fin de weekend',
                'Merci de votre présence dominicale'
            ]
        ];
        
        // Construction du message final
        $messageParts = [];
        
        // 1. Salutation de base
        $messageParts[] = $greetings[array_rand($greetings)];
        
        // 2. Nom de la personne
        $messageParts[] = "{$firstName} {$lastName}";
        
        // 3. Message contextuel selon le moment
        if ($timeContextMessages) {
            $messageParts[] = $timeContextMessages;
        }
        
        // 4. Message spécial pour chaque poste
        if ($jobTitle) {
            foreach ($roleMessages as $role => $messages) {
                if (str_contains($jobTitle, $role)) {
                    $messageParts[] = $messages[array_rand($messages)];
                    break;
                }
            }
        }
        
        // 5. Message selon le jour
        if (isset($dayMessages[$currentDay])) {
            $messageParts[] = $dayMessages[$currentDay][array_rand($dayMessages[$currentDay])];
        }
        
        // 6. Événements spéciaux
        $todayKey = date('m-d');
        if (isset($specialEvents[$todayKey])) {
            $messageParts[] = $specialEvents[$todayKey];
        }
        
        // 7. Message pour les jours fériés
        if ($isHoliday) {
            $messageParts[] = "Profitez de ce jour férié!";
        }
        
        // 8. Message pour le weekend
        if ($isWeekend) {
            $messageParts[] = "Bon courage pour ce jour de weekend!";
        }
        
        // Assemblage du message final
        return implode(', ', $messageParts) . '.';
    }
    
    // Exemple de fonction pour vérifier si c'est un jour férié en France
   // Exemple de fonction pour vérifier si c'est un jour férié en Côte d'Ivoire
private function isHoliday()
{
    // Liste des jours fériés en Côte d'Ivoire (format: 'm-d')
    $holidays = [
        '01-01', // Jour de l'an
        '04-11', // Fête Nationale
        '05-01', // Fête du Travail
        '08-07', // Fête de l'Indépendance
        '11-01', // Toussaint
        '11-15', // Journée Nationale de la Paix
        '12-25', // Noël
    ];
    
    // Calculer les jours fériés variables (Lundi de Pâques, Ascension, Pentecôte)
    $easterDate = easter_date(date('Y'));
    $holidays[] = date('m-d', $easterDate + 86400); // Lundi de Pâques (Pâques + 1 jour)
    $holidays[] = date('m-d', $easterDate + 39 * 86400); // Ascension (Pâques + 39 jours)
    $holidays[] = date('m-d', $easterDate + 50 * 86400); // Lundi de Pentecôte (Pâques + 50 jours)

    // Ajouter les jours fériés islamiques variables (format: 'm-d', ajuster les dates si nécessaire)
    // Les dates des fêtes islamiques varient chaque année et doivent être ajustées selon le calendrier lunaire
    $islamicHolidays = [
        '02-20', // Fête de la Tabaski (date à ajuster chaque année)
        '06-27', // Fête de l'Aïd el-Fitr (date à ajuster chaque année)
    ];
    $holidays = array_merge($holidays, $islamicHolidays);

    $today = date('m-d');
    return in_array($today, $holidays);
}
    
    
}
