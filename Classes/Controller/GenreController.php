<?php
namespace Slub\DmOnt\Controller;

use \Illuminate\Support\Collection;
use \Slub\DmOnt\Domain\Model\Genre;
use \Slub\DmNorm\Domain\Model\Work;
use \Slub\DmNorm\GndIdNotFoundException;
use \Slub\DmOnt\Lib\MvdbTerms;

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
 * GenreController
 */
class GenreController extends AbstractController
{

    /**
     * action list
     * 
     * @return void
     */
    public function listAction()
    {
        $isChamberMusic = function(Genre $genre): bool{
            return $genre->getChamberMusic();
        };
        $getId = function(Genre $genre): string {
            return $genre->getGndId();
        };
        $isLinked = function(array $work): bool {
            return $work['mvdb_links'][0] != null;
        };

        $mvdbGenres = $this->genreRepository->findAll();
        $genreArray = Collection::create($mvdbGenres->toArray());
        $links = $this->workRepository->listGenreLinks();
        $chamberMusicNodes = $genreArray
            ->filter($isChamberMusic)
            ->map($getId)
            ->implode(', ');

        $implemented = Collection::create($links)
            ->map($isLinked)
            ->reduceOr();
        $this->view->assign('chambermusicNodes', $chamberMusicNodes);
        $this->view->assign('mvdbGenres', $mvdbGenres);
        $this->view->assign('links', $links);
        $this->view->assign('implemented', $implemented);
    }

    /**
     * action import
     * 
     * @return void
     */
    public function importAction(
        string $filename, 
        string $subString, 
        string $superString
    )
    {
        $addToRepo = function($genre) {
            $this->mvdbGenreRepository->add($genre);
        };

        $rootGenre = $this->objectManager->get(Genre::class);
        $rootGenre->setName('error');
        $rootGenre->setRoot(true);
        $rootGenre->setBaseLevel(false);
        $rootGenre->setGndId('error');
        $addToRepo($rootGenre);

        $setRoot = function($genre) use ($rootGenre) {
            $genre->setSuperGenre($rootGenre);
        };
        $noSuper = function($genre) {
            return !$genre->getSuperGenre();
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
            $genres = [];

            while ($line != FALSE) {
                $relation = array_combine($keys, $line);
                [ $genres, $subGenre ] = MvdbTerms::makeOrFind( $relation[ $subString ], $genres, 'MvdbGenre' );
                [ $genres, $superGenre ] = MvdbTerms::makeOrFind( $relation[ $superString ], $genres, 'MvdbGenre' );
                $subGenre->setSuperGenre($superGenre);
                $line = fgetcsv($csvFile);
            }

            foreach ($genres as $genre) {
                if (!$genre->getSuperGenre()) {
                    $genre->setSuperGenre($rootGenre);
                }
                $this->genreRepository->add($genre);
            }

            fclose($csvFile);

        } catch (GndIdNotFoundException $e) {
        }

        $this->redirect('list');
    }

    /**
     * action export
     * 
     * @return void
     */
    public function exportAction()
    {
    }

    /**
     * action setChamberMusic
     * 
     * @return void
     */
    public function setChamberMusicAction(string $ids)
    {
        $idString = str_replace(' ', '', $ids);
        $idArray = explode(',', $idString);
        $setOff = function(Genre $genre) {
            $genre->setChamberMusic(false);
        };
        $setOn = function(Genre $genre) {
            $genre->setChamberMusic(true);
        };
        $save = function(Genre $genre) {
            $this->genreRepository->update($genre);
        };
        $filterId = function(Genre $genre) use ($idArray) {
            return in_array($genre->getGndId(), $idArray);
        };

        $genres = Collection::create($this
                ->genreRepository
                ->findAll()
                ->toArray());
        $genres
            ->each($setOff)
            ->filter($filterId)
            ->each($setOn);
        $genres->each($save);

        $this->redirect('list');
    }

    /**
     * action list
     * 
     * @return void
     */
    public function flushAction()
    {
        $this->genreRepository->removeAll();
        $this->redirect('list');
    }
}
