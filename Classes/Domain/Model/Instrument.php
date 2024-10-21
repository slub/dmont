<?php
namespace Slub\DmOnt\Domain\Model;


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
 * Instrument
 */
class Instrument extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * name
     * 
     * @var string
     */
    protected $name = '';

    /**
     * gndId
     * 
     * @var string
     */
    protected $gndId = '';

    /**
     * shorthand
     * 
     * @var string
     */
    protected $shorthand = '';

    /**
     * ensemble
     * 
     * @var bool
     */
    protected $ensemble = false;

    /**
     * voice
     * 
     * @var bool
     */
    protected $voice = false;

    /**
     * ignoreInName
     * 
     * @var bool
     */
    protected $ignoreInName = false;

    /**
     * baseLevel
     * 
     * @var bool
     */
    protected $baseLevel = false;

    /**
     * linked
     * 
     * @var bool
     */
    protected $linked = false;

    /**
     * root
     * 
     * @var bool
     */
    protected $root = false;

    /**
     * mappedIds
     * 
     * @var bool
     */
    protected $mappedIds = false;

    /**
     * superInstrument
     * 
     * @var \SLUB\DmOnt\Domain\Model\Instrument
     */
    protected $superInstrument = null;

    /**
     * Returns the name
     * 
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the gndId
     * 
     * @return string $gndId
     */
    public function getGndId()
    {
        return $this->gndId;
    }

    /**
     * Sets the gndId
     * 
     * @param string $gndId
     * @return void
     */
    public function setGndId($gndId)
    {
        $this->gndId = $gndId;
    }

    /**
     * Returns the shorthand
     * 
     * @return string $shorthand
     */
    public function getShorthand()
    {
        return $this->shorthand;
    }

    /**
     * Sets the shorthand
     * 
     * @param string $shorthand
     * @return void
     */
    public function setShorthand($shorthand)
    {
        $this->shorthand = $shorthand;
    }

    /**
     * Returns the superInstrument
     * 
     * @return \SLUB\DmOnt\Domain\Model\Instrument $superInstrument
     */
    public function getSuperInstrument()
    {
        return $this->superInstrument;
    }

    /**
     * Sets the superInstrument
     * 
     * @param \SLUB\DmOnt\Domain\Model\Instrument $superInstrument
     * @return void
     */
    public function setSuperInstrument(Instrument $superInstrument)
    {
        $this->superInstrument = $superInstrument;
    }

    /**
     * Returns the baseLevel
     * 
     * @return bool baseLevel
     */
    public function getBaseLevel()
    {
        return $this->baseLevel;
    }

    /**
     * Gets ensemble
     * 
     * @return bool
     */
    public function getEnsemble()
    {
        return $this->ensemble;
    }

    /**
     * Gets voice
     * 
     * @return bool
     */
    public function getVoice()
    {
        return $this->voice;
    }

    /**
     * Gets ignoreInName
     * 
     * @return bool
     */
    public function getIgnoreInName()
    {
        return $this->ignoreInName;
    }

    /**
     * Sets ensemble
     * 
     * @param bool $ensemble
     * @return void
     */
    public function setEnsemble(bool $ensemble)
    {
        $this->ensemble = $ensemble;
    }

    /**
     * Sets voice
     * 
     * @param bool $voice
     * @return void
     */
    public function setVoice(bool $voice)
    {
        $this->voice = $voice;
    }

    /**
     * Sets ignoreInName
     * 
     * @param bool $ignoreInName
     * @return void
     */
    public function setIgnoreInName(bool $ignoreInName)
    {
        $this->ignoreInName = $ignoreInName;
    }

    /**
     * Sets the baseLevel
     * 
     * @param string $baseLevel
     * @return void
     */
    public function setBaseLevel($baseLevel)
    {
        $this->baseLevel = $baseLevel;
    }

    /**
     * Returns the root
     * 
     * @return bool $root
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Sets the root
     * 
     * @param bool $root
     * @return void
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Returns the boolean state of root
     * 
     * @return bool
     */
    public function isRoot()
    {
        return $this->root;
    }

    /**
     * Returns the mappedIds
     * 
     * @return bool $mappedIds
     */
    public function getMappedIds()
    {
        return $this->mappedIds;
    }

    /**
     * Sets the mappedIds
     * 
     * @param bool $mappedIds
     * @return void
     */
    public function setMappedIds($mappedIds)
    {
        $this->mappedIds = $mappedIds;
    }

    /**
     * Returns the boolean state of mappedIds
     * 
     * @return bool
     */
    public function isMappedIds()
    {
        return $this->mappedIds;
    }

    /**
     * Returns the linked
     * 
     * @return bool $linked
     */
    public function getLinked()
    {
        return $this->linked;
    }

    /**
     * Sets the linked
     * 
     * @param bool $linked
     * @return void
     */
    public function setLinked($linked)
    {
        $this->linked = $linked;
    }
}
