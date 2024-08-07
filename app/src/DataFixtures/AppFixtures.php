<?php

namespace App\DataFixtures;

use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhase\Model\ReflexionPhase;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\VideoCode\Model\VideoCode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $exercise = $this->createExercise($manager, 'Analyse eines Videos zu einer Fördersituation', 'Analyse eines Videos zu einer Fördersituation, indem unter einem bestimmten Blickwinkel ein Analytical Shortfilm aus dem Video erstellt wird');
        $exercise_p1 = new VideoAnalysisPhase();
        $exercise_p1->setVideoAnnotationsActive(true);
        $exercise_p1->setVideoCodesActive(true);
        $exercise_p1->isGroupPhase = false;
        $exercise_p1->name = "Vorbereitung";
        $exercise_p1->task = '
        Studierende schauen sich die Videoszene
            („Roh“-Video im Moodle) an
            ‣ „Roh“-Video müsste in einen Ordner auf der Plattform hochgeladen und angeschaut werden können (mit den üblichen Funktionen wie stoppen/ vorspulen/ zurückspulen/ Zeitanzeige/...); asynchrone Bearbeitung, kein Produkt
            ▪ Studierende überlegen sich, welche Szenen Sie für den Analytical Shortfilm wählen würden (AB 1 - Vorbereitung der Videoszene)
        ';
        $exercise->addPhase($exercise_p1);

        $exercise_p2 = new VideoCutPhase();
        $exercise_p2->isGroupPhase = true;
        $exercise_p2->name = "Erstellen eines Analytical Shortfilms";
        $exercise_p2->task = '
            Erstellen Sie gemeinsam (in 3er-Gruppen) einen Analytical Shortfilm (Zusammenschnitt von 3-5 min. aus kurzen Szenen aus einem längeren „Roh“-Video) aus Ihrem Blickwinkel.
            a) Stellen Sie sich gegenseitig Ihre Szenenauswahl vor und diskutieren Sie diese. Dazu können Sie auch das Video noch einmal betrachten und an den entsprechenden Stellen stoppen.
            b) Einigen Sie sich auf gemeinsame Sequenzen. Schneiden Sie das Video entsprechend, so dass alle nicht relevanten Szenen gelöscht werden.
            c) Diskutieren Sie Ihre Begründungen.
            Nutzen Sie zur Dokumentation Ihrer Begründung die entsprechende Tabelle (AB 2 - Begründungen während der GA).
        ';
        $exercise->addPhase($exercise_p2);

        $exercise_p3 = new ReflexionPhase();
        $exercise_p3->name = "Austausch über die erstellten Filme";
        $exercise_p3->task = '
        Konkreter Arbeitsauftrag:
                a) Stellen Sie sich gegenseitig Ihre zusammengeschnittenen Filme (gleicher Blickwinkel) vor. Diskutieren Sie inhaltliche Gemeinsamkeiten und Unterschiede.
                b) Verallgemeinern Sie Ihre Erkenntnisse zu gelungenen und weniger gelungenen Fördermomenten, indem Sie aus den ausgewählten
                Szenen Ihrer Filme allgemeine Kennzeichen für gelungene bzw. weniger gelungene Fördermomente ableiten. Halten Sie Ihre Ergebnisse – jeder für sich – in der Tabelle fest (AB 4 Kennzeichen gelungener/ weniger gelungener Fördermomente).
        ';
        $exercise->addPhase($exercise_p3);
        $manager->persist($exercise);

        $this->createVideoCodePrototypes($manager);
        // create some more random exercises
        $this->createExercise($manager, 'Weitere Aufgabe', 'Beschreibung dieser Aufgabe');
        $this->createExercise($manager, 'Noch eine Aufgabe', 'Beschreibung dieser Aufgabe');
        $this->createExercise($manager, 'Und noch eine Aufgabe', 'Beschreibung dieser Aufgabe');

        $manager->flush();
    }

    /**
     * @param string $name
     * @param string $description
     * @return Exercise
     */
    private function createExercise(ObjectManager $manager, string $name, string $description): Exercise
    {
        $exercise = new Exercise();
        $exercise->name = $name;
        $exercise->description = $description;

        // this reference returns the User object created in UserFixtures
        $exercise->setCourse($this->getReference(AccountFixtures::COURSE_REFERENCE));
        $exercise->setCreator($this->getReference(AccountFixtures::CREATOR_REFERENCE));
        $manager->persist($exercise);
        return $exercise;
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    private function createVideoCodePrototypes(ObjectManager $manager): void
    {
        $videoCode1 = new VideoCode();
        $videoCode1->setName('Gelungene Momente der Förderung');
        $videoCode1->setColor('#4cdc70');

        $manager->persist($videoCode1);

        $videoCode2 = new VideoCode();
        $videoCode2->setName('Weniger gelungene Momente der Förderung');
        $videoCode2->setColor('#de3b3b');

        $manager->persist($videoCode2);
    }
}
