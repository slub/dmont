<?php
namespace Slub\DmOnt\Domain\Model;

use \Illuminate\Support\Collection;
use \Slub\DmOnt\Domain\Model\Instrument;
use \Slub\DmOnt\Domain\Model\Genre;

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
 * MediumOfPerformance
 */
class MediumOfPerformance extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * hasChoir
     * 
     * @var bool
     */
    protected $hasChoir = false;

    /**
     * hasOrchestra
     * 
     * @var bool
     */
    protected $hasOrchestra = false;

    /**
     * instrumentalSoloists
     * 
     * @var string
     */
    protected $instrumentalSoloists = '';

    /**
     * vocalSoloists
     * 
     * @var string
     */
    protected $vocalSoloists = '';

    /**
     * instrument
     * 
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SLUB\DmOnt\Domain\Model\Instrument>
     */
    protected $instrument = null;

    /**
     * Returns the name
     * 
     * @return string $name
     */
    public function getName()
    {
        $name = '';
        if ($this->hasOrchestra) {
            $name .= 'Orchester';
            if ($this->instrumentalSoloists) {
                $name .= ' mit ';
            }
        }
        if ($this->instrumentalSoloists) {
            $name .= $this->instrumentalSoloists;
        }
        if ($this->hasChoir) {
            if ($name) {
                $name .= ' und ';
            }
            $name .= 'Chor';
        }
        if ($this->vocalSoloists) {
            if ($name) {
                if ($this->hasChoir) {
                    $name .= ' mit ';
                } else {
                    $name .= ' und ';
                }
            }
            $name .= $this->vocalSoloists;
        }
        return $name;
    }

    /**
     * Sets the name
     * 
     * @return void
     */
    public function setName(array $genres)
    {
        $hasOrchestra = function(Instrument $instrument): bool {
            return $instrument->getEnsemble() && !$instrument->getVoice();
        };
        $hasChoir = function(Instrument $instrument): bool {
            return $instrument->getEnsemble() && $instrument->getVoice();
        };  
        $isVoice = function(Instrument $instrument): bool {
            return $instrument->getVoice();
        };

        $instruments = Collection::create($this->instrument->toArray());
        if ($instruments->map($hasOrchestra)->reduceOr()) {
            $this->hasOrchestra = true;
            $orchestra = $instruments->filter($hasOrchestra)->toArray();
            $instruments = $instruments->diff($orchestra);
        };
        if ($instruments->map($hasChoir)->reduceOr()) {
            $this->hasChoir = true;
            $choir = $instruments->filter($hasChoir)->toArray();
            $instruments = $instruments->diff($choir);
        };
        $voices = $instruments->filter($isVoice);
        $instruments = $instruments->diff($voices->toArray());
        $this->nameVoices($voices);
        $this->nameInstruments($instruments, $genres);
    }

    protected function nameInstruments(Collection $instruments, array $genres)
    {
        $isChamberMusic = function(Genre $genre): bool {
            return $genre->getChamberMusic();
        };
        $getSuperCategory = function(Instrument $instrument): Instrument {
            $outInstrument = $instrument;
            while ($outInstrument->getSuperInstrument() && !$outInstrument->getSuperInstrument()->getRoot()) {
                $outInstrument = $outInstrument->getSuperInstrument();
            }
            return $outInstrument;
        };

        $soloists = $instruments->values();
        if ($this->hasOrchestra) {
            if (count($soloists) == 1) {
                $this->instrumentalSoloists = $soloists[0]->getName();
            } else if (count($soloists) > 1) {
                $this->instrumentalSoloists = 'Instrumentalsolisten';
            }
        } else {
            if (count($soloists) == 1) {
                $this->instrumentalSoloists = 'Einzelinstrument ' .
                    $soloists[0]->getName();
            } else if (count($soloists) > 1) {
                $genre = Collection::create($genres);
                var_dump($genres);die;
                if (count($genre->filter($isChamberMusic)->values())) {
                    $this->instrumentalSoloists = $genre
                        ->filter($isChamberMusic)
                        ->values()[0]
                        ->getName();
                } else {
                    $this->instrumentalSoloists = 'gemischtes Ensemble';
                    $superCategories = $instruments
                        ->map($getSuperCategory)
                        ->unique()
                        ->values();
                    if (count($superCategories) == 1) {
                        if ($superCategories[0]->getName() == 'Streichinstrument') {
                            $this->instrumentalSoloists = 'Streichensemble';
                        }
                        if ($superCategories[0]->getName() == 'Blasinstrument') {
                            $this->instrumentalSoloists = 'Blasensemble';
                        }
                    }
                }
            }
        }
    }

    protected function nameVoices(Collection $voices)
    {
        $soloists = $voices->values();
        if (count($soloists) == 1) {
            $this->vocalSoloists = $soloists[0]->getName() . ' Solo';
        } else if (count($soloists) > 1) {
            $this->vocalSoloists = 'Vokalsolisten';
        }
    }

    /**
     * __construct
     */
    public function __construct()
    {

        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     * 
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->instrument = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a Instrument
     * 
     * @param \SLUB\DmOnt\Domain\Model\Instrument $instrument
     * @return void
     */
    public function addInstrument(Instrument $instrument)
    {
        $this->instrument->attach($instrument);
    }

    /**
     * Removes a Instrument
     * 
     * @param \SLUB\DmOnt\Domain\Model\Instrument $instrumentToRemove The Instrument to be removed
     * @return void
     */
    public function removeInstrument(Instrument $instrumentToRemove)
    {
        $this->instrument->detach($instrumentToRemove);
    }

    /**
     * Returns the instrument
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SLUB\DmOnt\Domain\Model\Instrument> $instrument
     */
    public function getInstrument()
    {
        return $this->instrument;
    }

    /**
     * Sets the instrument
     * 
     * @param array $instruments
     * @return void
     */
    public function setInstrument(array $instruments)
    {
        $store = function (Instrument $mvdbInstrument = null) {
            if ($mvdbInstrument) {
                $this->instrument->attach($mvdbInstrument);
            }
        };

        Collection::create($instruments)
            ->each($store);
    }
}
