<?php
namespace Slub\DmOnt\Domain\Model;

use \Slub\DmOnt\Domain\Repository\GenreRepository;
use \Illuminate\Support\Collection;

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
 * Genre
 */
class Genre extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * genreRepository
     * 
     * @var GenreRepository
     */
    protected $genreRepository = null;

    /**
     * chamberMusic
     * 
     * @var bool
     */
    protected $chamberMusic = false;

    /**
     * @param GenreRepository $genreRepository
     */
    public function injectGenreRepository(GenreRepository $genreRepository)
    {
        $this->genreRepository = $genreRepository;
    }


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
     * baseLevel
     * 
     * @var bool
     */
    protected $baseLevel = '';

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
     * @var string
     */
    protected $mappedIds = '';

    /**
     * superGenre
     * 
     * @var \SLUB\DmOnt\Domain\Model\Genre
     */
    protected $superGenre = null;

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
     * Sets chamberMusic
     * 
     * @param bool $chamberMusic
     * @return void
     */
    public function setChamberMusic(bool $chamberMusic)
    {
        $this->chamberMusic = $chamberMusic;
    }

    /**
     * Gets chamberMusic
     * 
     * @return bool
     */
    public function getChamberMusic()
    {
        return $this->chamberMusic;
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
     * Returns the superGenre
     * 
     * @return \SLUB\DmOnt\Domain\Model\Genre $superGenre
     */
    public function getSuperGenre()
    {
        return $this->superGenre;
    }

    /**
     * Sets the superGenre
     * 
     * @param \SLUB\DmOnt\Domain\Model\Genre $superGenre
     * @return void
     */
    public function setSuperGenre(Genre $superGenre)
    {
        $this->superGenre = $superGenre;
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
     * Sets the baseLevel
     * 
     * @param string $baseLevel
     * @return void
     */
    public function setBaseLevel($baseLevel)
    {
        $unsetBaseLevel = function($genre) {
            $genre->setBaseLevel(false);
            $this->genreRepository->update($genre);
        };

        $this->baseLevel = $baseLevel;
        if ($baseLevel) {
            Collection::create( $this->genreRepository->findBySuperGenre($this)->toArray() )
                ->each( $unsetBaseLevel );
        }
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
     * @return string $mappedIds
     */
    public function getMappedIds()
    {
        return $this->mappedIds;
    }

    /**
     * Sets the mappedIds
     * 
     * @param string $mappedIds
     * @return void
     */
    public function setMappedIds($mappedIds)
    {
        $this->mappedIds = $mappedIds;
    }
}
