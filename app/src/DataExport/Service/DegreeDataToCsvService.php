<?php

namespace App\DataExport\Service;

use App\DataExport\Dto\TextFileDto;
use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideAnnotation;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideVideoCode;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideVideoCodePrototype;
use App\Domain\User\Model\User;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * This service allows us to aggregate degree data in a way that we can export it to CSV files.
 * We do not handle the files themselves in here - this should be done outside of the service.
 * Each file is simply represented as a @see {TextFileDto} which just contains a fileName as well
 * as a contentString.
 *
 * Also make sure that you update the README.md accordingly if you change columns etc. inside
 * those files.
 */
class DegreeDataToCsvService
{
    private const string DEFAULT_DELIMITER = ';';
    private const string DEFAULT_ENCLOSURE = '"';
    private const array DEFAULT_ENCODING_CONTEXT = [
        CsvEncoder::DELIMITER_KEY => self::DEFAULT_DELIMITER,
        CsvEncoder::ENCLOSURE_KEY => self::DEFAULT_ENCLOSURE,
        CsvEncoder::NO_HEADERS_KEY => true,
    ];

    public function __construct()
    {
    }

    private static function removeLineBreaksFromCellContent(string $cellContent): string
    {
        return str_replace(["\n", "\r"], " ", $cellContent);
    }

    /**
     * @return TextFileDto[]
     */
    public function getAllAsVirtualCSVs(Course $course): array
    {
        $serializer = new Serializer([], [new CsvEncoder()]);

        $solutionData = $this->getSolutionData($course);
        $solutionCSV = $serializer->encode($solutionData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        $courseUserData = $this->getCourseUserData($course);
        $courseUserCSV = $serializer->encode($courseUserData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        $teamUserData = $this->getTeamUserData($course);
        $teamUserCSV = $serializer->encode($teamUserData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        $annotationData = $this->getAnnotationData($course);
        $annotationCSV = $serializer->encode($annotationData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        $videoCodeData = $this->getVideoCodeData($course);
        $videoCodeCSV = $serializer->encode($videoCodeData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        $cutData = $this->getCutData($course);
        $cutCSV = $serializer->encode($cutData, 'csv', self::DEFAULT_ENCODING_CONTEXT);

        return [
            new TextFileDto('README.md', $this->getREADME()),
            new TextFileDto('loesungen.csv', $solutionCSV),
            new TextFileDto('kurs-mitglieder.csv', $courseUserCSV),
            new TextFileDto('team-mitglieder.csv', $teamUserCSV),
            new TextFileDto('annotationen.csv', $annotationCSV),
            new TextFileDto('video-kodierungen.csv', $videoCodeCSV),
            new TextFileDto('schnitte.csv', $cutCSV)
        ];
    }

    public function getVideoCodeData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $solutionDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $exercisePhase = $team->getExercisePhase();
            $solution = $team->getSolution();

            if (empty($exercisePhase) || empty($solution)) {
                return $carry;
            }

            $solutionData = $solution->getSolution();
            $videoCodes = $solutionData->getVideoCodes();

            // VideoCodePrototypes may contain child prototypes.
            // To be able to lookup a specific prototype we flatten them into a single list
            // of server side prototypes.
            $nestedServerSideVideoCodePrototypes = $solutionData->getVideoCodePrototypes();
            $flattenedServerSideVideoCodePrototypes = array_reduce($nestedServerSideVideoCodePrototypes, function (array $carry, ServerSideVideoCodePrototype $serverSidePrototype) {
                $childServerSidePrototypes = $serverSidePrototype->getChildServerSidePrototypes();
                return array_merge($carry, $childServerSidePrototypes);
            }, $nestedServerSideVideoCodePrototypes);

            $rows = array_map(function (ServerSideVideoCode $serverSideVideoCode) use ($solution, $flattenedServerSideVideoCodePrototypes) {
                /** @var ServerSideVideoCodePrototype $videoCodePrototype * */
                $videoCodePrototype = current(array_filter($flattenedServerSideVideoCodePrototypes, function ($prototype) use ($serverSideVideoCode) {
                    return $prototype->getId() == $serverSideVideoCode->getIdFromPrototype();
                }));

                $prototypeData = $videoCodePrototype
                    ? [
                        $videoCodePrototype->getName(),
                        $videoCodePrototype->getColor(),
                        $videoCodePrototype->getParentId(),
                        $videoCodePrototype->getUserCreated() ? 'ja' : 'nein'
                    ]
                    : [];

                return array_merge(
                    [$solution->getId()],
                    [
                        $serverSideVideoCode->getStart(),
                        $serverSideVideoCode->getEnd(),
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideVideoCode->getText()),
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideVideoCode->getMemo()),
                        $serverSideVideoCode->getColor(),
                        $serverSideVideoCode->getIdFromPrototype(),
                    ],
                    $prototypeData,
                );
            }, $videoCodes);

            return array_merge($carry, $rows);
        }, [
            // CSV headers
            [
                // Solution
                "loesungsID",

                // VideoCode
                "start",
                "end",
                "text",
                "memo",
                "farbe",
                "codeID",

                // VideoCodePrototype
                "codeName",
                "codeFarbe",
                "elternCodeID",
                "selbstErstellterCode",
            ],
        ]);

        return $solutionDataRows;
    }

    public function getAnnotationData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $solutionDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $exercisePhase = $team->getExercisePhase();
            $solution = $team->getSolution();

            if (empty($exercisePhase) || empty($solution)) {
                return $carry;
            }

            $solutionData = $solution->getSolution();
            $annotations = $solutionData->getAnnotations();

            $rows = array_map(function (ServerSideAnnotation $serverSideAnnotation) use ($solution) {
                return array_merge(
                    [$solution->getId()],
                    [
                        $serverSideAnnotation->getStart(),
                        $serverSideAnnotation->getEnd(),
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideAnnotation->getText()),
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideAnnotation->getMemo()),
                        $serverSideAnnotation->getColor(),
                    ],
                );
            }, $annotations);

            return array_merge($carry, $rows);
        }, [
            // CSV headers
            [
                "loesungsID",
                "start",
                "end",
                "text",
                "memo",
                "farbe"
            ],
        ]);

        return $solutionDataRows;
    }

    public function getCutData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $solutionDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $exercisePhase = $team->getExercisePhase();
            $solution = $team->getSolution();

            if (empty($exercisePhase) || empty($solution)) {
                return $carry;
            }

            $solutionData = $solution->getSolution();
            $cuts = $solutionData->getCutList();

            $rows = array_map(function (ServerSideCut $serverSideCut) use ($solution) {
                return array_merge(
                    [$solution->getId()],
                    [
                        $serverSideCut->start,
                        $serverSideCut->end,
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideCut->text),
                        DegreeDataToCsvService::removeLineBreaksFromCellContent($serverSideCut->memo),
                        $serverSideCut->color,
                    ]
                );
            }, $cuts);

            return array_merge($carry, $rows);
        }, [
            // CSV headers
            [
                "loesungsID",
                "start",
                "end",
                "text",
                "memo",
                "farbe",
            ],
        ]);

        return $solutionDataRows;
    }

    public function getTeamUserData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $teamDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $solution = $team->getSolution();
            $solutionId = $solution ? $solution->getId() : "Keine Lösung vorhanden";

            $teamUsers = $team->getMembers()->toArray();

            $rows = array_map(function (User $teamUser) use ($solutionId, $team) {
                return [
                    // User
                    $teamUser->getId(),
                    $teamUser->getEmail(),

                    // Team
                    $team->getId(),
                    $team->getCreator()->getId(),
                    $solutionId,
                ];
            }, $teamUsers);

            return array_merge($carry, $rows);
        }, [
            // CSV headers
            [
                // User
                "nutzerID",
                "nutzerName",

                // team
                "teamID",
                "teamErstellerID",
                "loesungsID"
            ],
        ]);

        return $teamDataRows;
    }

    public function getCourseUserData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $courseDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $exercisePhase = $team->getExercisePhase();

            if (empty($exercisePhase)) {
                return $carry;
            }

            $exercise = $exercisePhase->getBelongsToExercise();
            $course = $exercise->getCourse();
            $courseRoles = $course->getCourseRoles()->toArray();

            $rows = array_map(function (CourseRole $courseRole) use ($course) {
                return [
                    $course->getId(),
                    $course->getName(),
                    $courseRole->getName(),
                    $courseRole->getUser()->getEmail(),
                ];
            }, $courseRoles);

            return array_merge($carry, $rows);
        }, [
            // CSV headers
            [
                // Course
                "kursID",
                "kursName",
                "kursRolle",
                "nutzerName",
            ],
        ]);

        return $courseDataRows;
    }

    public function getSolutionData(Course $course): array
    {
        $exercisePhaseTeams = $this->getExercisePhaseTeamsByCourse($course);

        $solutionDataRows = array_reduce($exercisePhaseTeams, function ($carry, ExercisePhaseTeam $team) {
            $exercisePhase = $team->getExercisePhase();

            if (empty($exercisePhase)) {
                return $carry;
            }

            $exercise = $exercisePhase->getBelongsToExercise();
            $course = $exercise->getCourse();
            $solution = $team->getSolution();
            $previousPhase = $exercisePhase->getDependsOnExercisePhase();

            return array_merge($carry, [[
                // Solution
                $solution ? $solution->getId() : "Keine Lösung vorhanden",

                // Course
                $course->getId(),
                $course->getName(),

                // Exercise
                $exercise->getId(),
                $exercise->getName(),
                DegreeDataToCsvService::removeLineBreaksFromCellContent($exercise->getDescription()),
                $exercise->getCreatedAt()->format("d.m.Y"),
                $this->getExerciseStatus($exercise->getStatus()),

                // ExercisePhase
                $exercisePhase->getId(),
                $exercisePhase->isGroupPhase() ? 'Ja' : 'Nein',
                $exercisePhase->getName(),
                DegreeDataToCsvService::removeLineBreaksFromCellContent($exercisePhase->getTask()),
                $exercisePhase->getType()->value,
                $exercisePhase->getDependsOnExercisePhase() !== null ? 'Ja' : 'Nein',
                $previousPhase ? $previousPhase->getId() : '-',

                // Team
                $team->getId(),
                $team->getCreator()->getEmail(),
            ]]);
        }, [
            // CSV headers
            [
                // Solution
                "loesungsID",

                // Course
                "kursID",
                "kursName",

                // Exercise
                "aufgabenID",
                "aufgabenTitel",
                "aufgabenBeschreibung",
                "erstellungsDatum",
                "status",

                // ExercisePhase
                "phasenID",
                "istGruppenphase",
                "phasenTitel",
                "phasenBeschreibung",
                "phasenTyp",
                "bautAufVorherigerPhaseAuf",
                "vorherigePhasenID",

                // Team
                "teamID",
                "teamErsteller",
            ],
        ]);

        return $solutionDataRows;
    }

    private function getREADME(): string
    {
        $content = <<<'EOT'
# README

## Import der CSV-Dateien in gängige Programme

Damit die Dateien und ihre Spalten korrekt importiert werden. Müssen folgende Dinge beachtet werden:

1. Das verwendete Trennzeichen ist das Semikolon `;` und muss ggf. beim Import (bspw. in Excel) manuell konfiguriert werden
2. Der verwendete Textidentifizierer sind doppelte Anführungszeichen `"` und müssen ebenfalls ggf. konfiguriert werden


### Import in Excel

Um eine CSV-Datei in Excel korrekt anzuzeigen, muss diese über die Funktion "Importieren" geladen werden.
Ein einfaches öffnen der Datei **ist nicht ausreichend**!
Beim Import können dann **CSV** als Format ausgewählt und die oben beschriebenen Einstellungen vorgenommen werden.


## Aufbau der Dateien

Da CSV-Dateien nur sehr bedingt dazu in der Lage sind relationale Daten abzubilden, sind die exportierten Daten auf
mehrere Dateien aufgeteilt.
Sind alle Dateien einmal in ein Programm (bspw. Excel) importiert worden, können die Daten beliebig untereinander verknüpft werden.
Zu diesem Zweck sind je nach Datei verschiedene Spalten mit IDs enthalten, welche eine Verknüpfung ermöglichen.
Im Folgenden wird ein allgemeiner Überblick über den Aufbau jeder einzelnen Datei gegeben.


### loesungen.csv

Jede Zeile in dieser Datei repräsentiert eine Lösung.
Eine Lösung wurde entsprechend von einem Team bestehen aus verschiedenen Nutzer:innen im Rahmen einer Aufgabe gelöst.

> **ACHTUNG** Zugehörige Annotationen/Kodierungen/Schnitte liegen entsprechend in ihren eigenen CSV-Dateien und
> können über die loesungsID referenziert werden!

#### Spaltenübersicht

- **loesungsID**: Identifier der Loesung
- **kursID**: Identifier des Kurses, zu welchem die Aufgabe gehört
- **kursName**: Name des Kurses, zu welchem die Aufgabe gehört
- **aufgabenID**: Identifier der Aufgabe
- **aufgabenTitel**: Title der Aufgabe
- **aufgabenBeschreibung**: Beschreibung der Aufgabe
- **erstellungsDatum**: Erstellungsdatum der Aufgabe
- **status**: Status der Aufgabe
- **phasenID**: Identifier der Aufgaben-Phase, zu welcher die Lösung gehört
- **istGruppenphase**: Bestimmt, ob es sich um eine Gruppenphase handelt
- **phasenTitel**: Titel der Aufgaben-Phase, zu welcher die Lösung gehört
- **phasenBeschreibung**: Beschreibung der Aufgaben-Phase, zu welcher die Lösung gehört
- **phasenTyp**: Typ der Aufgaben-Phase, zu welcher die Lösung gehört
- **bautAufVorherigerPhaseAuf**: Bestimmt, ob die Phase auf ihrer vorherigen aufbaut
- **vorherigePhasenID**: Identifier der vorherigen Phase (wird nur angezeigt, wenn die aktuell Phase auf ihrer vorherigen aufbaut)
- **teamID**: Identifier des Teams, welches die Lösung erstellt hat (ein Team kann auch nur aus einer einzigen Person bestehen!)
- **teamErsteller**: Nutzername der Nutzer:in, welche das Team dieser Lösung erstellt hat


### kurs-mitglieder.csv

In dieser Datei sind alle Mitglieder des exportierten Kurses aufgelistet.
Eine Zeile repräsentiert entsprechend eine Nutzer:in.

#### Spaltenübersicht

- **kursID**: Identifier des Kurses, zu welchem die Nutzer:in gehört
- **kursName**: Name des Kurses, zu welchem die Nutzer:in gehört
- **kursRolle**: Rolle, welche die Nutzer:in in diesem Kurs inne hat (DOZENT oder STUDENT)
- **nutzerName**: Name der Nutzer:in


### team-mitglieder.csv

Jeder Lösung wird von genau einem Team erstellt.
Diese Datei gibt eine Übersicht über die zum Team gehörenden Nutzer:innen.
Jede Zeile repräsentiert entsprechend eine Nutzer:in.

#### Spaltenübersicht

- **nutzerID**: Identifier der Nutzer:in
- **nutzerName**: Name der Nutzer:in
- **teamID**: Identifier des Teams, zu welchem die Nutzerin gehört
- **teamErstellerID**: Identifier der Ersteller:in des Teams
- **loesungsID**: Identifier der Lösung, zu welchem das Team gehört


### annotationen.csv

Annotationen sind immer Teil einer Lösung.
Jede Zeile in dieser Datei repräsentiert eine Annotation.
Um die dazugehörige Lösung zu finden, sollte eine Auswertung erstellt werden,
welche die Dateien `annotationen.csv` und `loesungen.csv` über die "loesungsID" miteinander verknüpft.

#### Spaltenübersicht

- **loesungsID**: Identifier der Lösung zu welcher die Annotation gehört
- **start**: Startzeit der Annotation im Video
- **end**: Endzeit der Annotation im Video
- **text**: Text der Annotation
- **memo**: Hinterlegter Memotext der Annotation
- **farbe**: Farbe der Annotation (wird momentan nicht genutzt)


### video-kodierungen.csv

VideoKodierungen sind immer Teil einer Lösung.
Jede Zeile in dieser Datei repräsentiert eine VideoKodierung.
Um die dazugehörige Lösung zu finden, sollte eine Auswertung erstellt werden,
welche die Dateien `video-kodierungen.csv` und `loesungen.csv` über die "loesungsID" miteinander verknüpft.

#### Spaltenübersicht

- **loesungsID**: Identifier der Lösung zu welcher die VideoKodierung gehört
- **start**: Startzeit der VideoKodierung im Video
- **end**: Endzeit der VideoKodierung im Video
- **text**: Text der VideoKodierung
- **memo**: Hinterlegter Memotext der VideoKodierung
- **farbe**: Farbe der VideoKodierung (wird momentan nicht genutzt)
- **codeID**: Identifier des zur Kodierung gehörenden Codes
- **codeName**: Name des zur Kodierung gehörenden Codes
- **codeFarbe**: Farbe des zur Kodierung gehörenden Codes
- **elternCodeID**: Identifier des Eltern-Codes (sofern vorhanden)
- **selbstErstellterCode**: Bestimmt, ob es sich um einen an der Phase vordefinierten oder von Nutzer:innen selbst erstellten Code handelt


### schnitte.csv

Schnitten sind immer Teil einer Lösung.
Jede Zeile in dieser Datei repräsentiert eine Schnitt.
Um die dazugehörige Lösung zu finden, sollte eine Auswertung erstellt werden,
welche die Dateien `annotationen.csv` und `loesungen.csv` über die "loesungsID" miteinander verknüpft.

#### Spaltenübersicht

- **loesungsID**: Identifier der Lösung zu welcher die Schnitt gehört
- **start**: Startzeit der Schnitt im Video
- **end**: Endzeit der Schnitt im Video
- **text**: Text der Schnitt
- **memo**: Hinterlegter Memotext der Schnitt
- **farbe**: Farbe der Schnitt (wird momentan nicht genutzt)
EOT;
        return $content;
    }

    // WHY:
    // Linebreaks might lead to broken csv rows (e.g. upon importing them into MS excel etc.)

    private function getExercisePhaseTeamsByCourse(Course $course)
    {
        $exercises = $course->getExercises()->toArray();
        return array_reduce($exercises, function ($carry, Exercise $exercise) {
            $phases = $exercise->getPhases()->toArray();
            $teams = array_reduce($phases, function ($carry, ExercisePhase $phase) {
                return array_merge($carry, $phase->getTeams()->toArray());
            }, []);

            return array_merge($carry, $teams);
        }, []);
    }

    private function getExerciseStatus(int $statusCode): string
    {
        return ["created", "published", "finished"][$statusCode];
    }
}
