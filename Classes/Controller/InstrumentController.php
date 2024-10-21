<?php
namespace Slub\PublisherDb\Controller;

use Slub\PublisherDb\Domain\Model\MvdbInstrument;
use Slub\PublisherDb\Lib\MvdbTerms;
use Slub\PublisherDb\Lib\DbArray;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***
 *
 * This file is part of the "Publisher Database" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Matthias Richter <matthias.richter@slub-dresden.de>, SLUB Dresden
 *
 ***/
/**
 * MvdbInstrumentController
 */
class MvdbInstrumentController extends AbstractController
{

    const satbMap = [
        'Sopran' => 'S',
        'Alt' => 'A',
        'Tenor' => 'T',
        'Bass' => 'B',
        'Kontrabass' => 'Kb' ];

    /**
     * action list
     * 
     * @return void
     */
    public function listAction()
    {
        $isEnsemble = function(MvdbInstrument $instrument): bool{
            return $instrument->getEnsemble();
        };
        $isVoice = function(MvdbInstrument $instrument): bool{
            return $instrument->getVoice();
        };
        $isIgnoreInName = function(MvdbInstrument $instrument): bool{
            return $instrument->getIgnoreInName();
        };
        $getId = function(MvdbInstrument $instrument): string {
            return $instrument->getGndId();
        };
        $isLinked = function(array $work): bool {
            return $work['mvdb_links'][0] != null;
        };

        $mvdbInstruments = $this->mvdbInstrumentRepository->findAll();
        $instrumentArray = (new DbArray())
            ->set($mvdbInstruments->toArray());
        $ensembleNodes = $instrumentArray
            ->filter($isEnsemble)
            ->map($getId)
            ->implode(', ');
        $voiceNodes = $instrumentArray
            ->filter($isVoice)
            ->map($getId)
            ->implode(', ');
        $ignoreNodes = $instrumentArray
            ->filter($isIgnoreInName)
            ->map($getId)
            ->implode(', ');

        $links = $this->workRepository->listInstrumentLinks();
        $implemented = (new DbArray())
            ->set($links)
            ->map($isLinked)
            ->reduceOr();

        $links = $this->workRepository->listInstrumentLinks();
        $this->view->assign('mvdbInstruments', $mvdbInstruments);
        $this->view->assign('links', $links);
        $this->view->assign('ensembleNodes', $ensembleNodes);
        $this->view->assign('voiceNodes', $voiceNodes);
        $this->view->assign('ignoreNodes', $ignoreNodes);
        $this->view->assign('implemented', $implemented);
    }

    /**
     * action import
     * 
     * @return void
     */
    public function importAction(string $filename, string $subString, string $superString)
    {
        $addToRepo = function($instrument) {
            $this->mvdbInstrumentRepository->add($instrument);
        };

        $rootInstrument = $this->objectManager->get(MvdbInstrument::class);
        $rootInstrument->setName('error');
        $rootInstrument->setRoot(true);
        $rootInstrument->setBaseLevel(false);
        $rootInstrument->setGndId('error');
        $addToRepo($rootInstrument);

        $setRoot = function($instrument) use ($rootInstrument) {
            $instrument->setSuperInstrument($rootInstrument);
        };
        $noSuper = function($instrument) {
            return !$instrument->getSuperInstrument();
        };

        $path = '/var/www/';
        // switch ddev/live system
        $path .= is_dir($path . 'webroot') ? 'webroot/' : 'html/';
        $path .= 'public/fileadmin/Sachbegriffe/';

        try {
            $csvFile = fopen($path . $filename, "r");
            $line = fgetcsv($csvFile);
            $keys = $line;
            $line = fgetcsv($csvFile);
            $instruments = [];

            while ($line != FALSE) {
                $relation = array_combine($keys, $line);
                [ $instruments, $subInstrument ] = MvdbTerms::makeOrFind( $relation[ $subString ], $instruments, 'MvdbInstrument' );
                [ $instruments, $superInstrument ] = MvdbTerms::makeOrFind( $relation[ $superString ], $instruments, 'MvdbInstrument' );
                $subInstrument->setSuperInstrument($superInstrument);
                $line = fgetcsv($csvFile);
            }

            $instruments = $this->makeShorthands($instruments);
            foreach ($instruments as $instrument) {
                if (!$instrument->getSuperInstrument()) {
                    $instrument->setSuperInstrument($rootInstrument);
                }
                $this->mvdbInstrumentRepository->add($instrument);
            }

            fclose($csvFile);

        } catch (GndIdNotFoundException $e) {
        }

        $this->redirect('list');
    }
    private function debug () {hallo();}

    private function makeShorthands($instruments) {
        $outInstruments = [];
        $shMap = [];
        $open = [];
        $nameMap = [];

        // check for unique shorthands in input
        /*
        foreach ($instruments as $instrument) {
            $shorthand = $instrument->getShorthand();
            if ($shorthand && !array_key_exists($shorthand, $shMap)) {
                $shMap[$shorthand] = $instrument;
                $outInstruments[] = $instrument;
            } else {
                $open[] = $instrument;
            }
            $nameMap[$instrument->getName()] = strtolower($instrument->getShorthand());
        }
         */

        // try to translate SATB to shorthand
        /*
        foreach ($instruments as $instrument) {
            if (!$instrument->getShorthand) {
                foreach (self::satbMap as $long => $short) {
                    $name = $instrument->getName();
                    if (str_contains($name, $long)) {
                        $rest = explode($long, $name)[1];
                        if (array_key_exists(strtolower($rest), $nameMap)) {
                            $instrument->setShorthand($short . $nameMap[$rest]);
                            $outInstruments[] = $instrument;
                            break;
                        }
                    }
                }
            }
        }
         */

        // generate unique shorthands from names
        /*
        $l = 3;
        while ($instruments) {
            $buffer = [];
            foreach ($instruments as $instrument) {
                $shorthand = mb_substr($instrument->getName(), 0, $l);
                if (!array_key_exists($shorthand, $shMap)) {
                    //$other = $shMap[$shorthand];
                    //$shMap = array_diff($shMap, [ $other ]);
                    //$outInstruments = array_diff($outInstruments, [ $other ]);
                    //$instruments[] = $other;
                    $instrument->setShorthand($shorthand);
                    $shMap[$shorthand] = $instrument;
                    $outInstruments[] = $instrument;
                    //$instruments = array_diff($instruments, [ $instrument ]);
                }// else {
                //}
            }
            $instruments = array_diff($instruments, $outInstruments);
            $l++;
        }
         */

        // make shorthands camelcase ready for imploding in instrumentations
        foreach ($instruments as $instrument) {
            $instrument->setShorthand($instrument->getName());
            $prelimShorthand = str_split($instrument->getShorthand());
            $shorthand = '';
            for ($i = 0; $i < count($prelimShorthand); $i++) {
                if ($i == 0) {
                    $shorthand .= strtoupper( $prelimShorthand[$i] );
                } else {
                    $shorthand .= strtolower( $prelimShorthand[$i] );
                }
            }
            $instrument->setShorthand($shorthand);
            //if ($shorthand == '\xC3') $this->debug($instrument->getName(), $instrument->getGndId());
        }

        return $instruments;
    }

    /**
     * action setVoice
     * 
     * @return void
     */
    public function setVoiceAction(string $ids)
    {
        $idString = str_replace(' ', '', $ids);
        $idArray = explode(',', $idString);
        $setOff = function(MvdbInstrument $instrument) {
            $instrument->setVoice(false);
        };
        $setOn = function(MvdbInstrument $instrument) {
            $instrument->setVoice(true);
        };
        $save = function(MvdbInstrument $instrument) {
            $this->mvdbInstrumentRepository->update($instrument);
        };
        $filterId = function(MvdbInstrument $instrument) use ($idArray) {
            return in_array($instrument->getGndId(), $idArray);
        };

        $instruments = (new DbArray())
            ->set($this
                ->mvdbInstrumentRepository
                ->findAll()
                ->toArray());
        $instruments
            ->each($setOff)
            ->filter($filterId)
            ->each($setOn);
        $instruments->each($save);

        $this->redirect('list');
    }

    /**
     * action setIgnoreInNames
     * 
     * @return void
     */
    public function setIgnoreInNamesAction(string $ids)
    {
        $idString = str_replace(' ', '', $ids);
        $idArray = explode(',', $idString);
        $setOff = function(MvdbInstrument $instrument) {
            $instrument->setIgnoreInName(false);
        };
        $setOn = function(MvdbInstrument $instrument) {
            $instrument->setIgnoreInName(true);
        };
        $save = function(MvdbInstrument $instrument) {
            $this->mvdbInstrumentRepository->update($instrument);
        };
        $filterId = function(MvdbInstrument $instrument) use ($idArray) {
            return in_array($instrument->getGndId(), $idArray);
        };

        $instruments = (new DbArray())
            ->set($this
                ->mvdbInstrumentRepository
                ->findAll()
                ->toArray());
        $instruments
            ->each($setOff)
            ->filter($filterId)
            ->each($setOn);
        $instruments->each($save);

        $this->redirect('list');
    }

    /**
     * action setEnsemble
     * 
     * @return void
     */
    public function setEnsembleAction(string $ids)
    {
        $idString = str_replace(' ', '', $ids);
        $idArray = explode(',', $idString);
        $setOff = function(MvdbInstrument $instrument) {
            $instrument->setEnsemble(false);
        };
        $setOn = function(MvdbInstrument $instrument) {
            $instrument->setEnsemble(true);
        };
        $save = function(MvdbInstrument $instrument) {
            $this->mvdbInstrumentRepository->update($instrument);
        };
        $filterId = function(MvdbInstrument $instrument) use ($idArray) {
            return in_array($instrument->getGndId(), $idArray);
        };

        $instruments = (new DbArray())
            ->set($this
                ->mvdbInstrumentRepository
                ->findAll()
                ->toArray());
        $instruments
            ->each($setOff)
            ->filter($filterId)
            ->each($setOn);
        $instruments->each($save);

        $this->redirect('list');
    }

    /**
     * action flushAction
     *
     * @return void
     */
    public function flushAction()
    {
        $this->mvdbInstrumentRepository->removeAll();
        $this->redirect('list');
    }
}
