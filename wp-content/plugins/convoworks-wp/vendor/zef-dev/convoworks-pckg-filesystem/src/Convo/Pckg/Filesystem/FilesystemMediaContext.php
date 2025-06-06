<?php

namespace Convo\Pckg\Filesystem;

use Convo\Core\Workflow\AbstractMediaSourceContext;
class FilesystemMediaContext extends AbstractMediaSourceContext
{
    const MIN_MATCH_PERCENT = 80;
    private $_basePath;
    private $_baseUrl;
    private $_minMatchPercentage;
    private $_search;
    private $_searchFolders;
    private $_backgroundUrl;
    private $_defaultSongImageUrl;
    private $_loadedSongs;
    public function __construct($properties)
    {
        parent::__construct($properties);
        $this->_basePath = $properties['base_path'];
        $this->_baseUrl = $properties['base_url'];
        $this->_minMatchPercentage = $properties['min_match_percentage'] ?? self::MIN_MATCH_PERCENT;
        $this->_search = $properties['search'];
        $this->_searchFolders = $properties['search_folders'];
        $this->_backgroundUrl = $properties['background_url'];
        $this->_defaultSongImageUrl = $properties['default_song_image_url'];
    }
    // MEDIA
    public function getCount() : int
    {
        if (isset($this->_loadedSongs)) {
            return \count($this->_loadedSongs);
        }
        return \iterator_count($this->getSongs());
    }
    // QUERY
    public function getSongs()
    {
        $base_path = $this->getService()->evaluateString($this->_basePath);
        $base_url = $this->getService()->evaluateString($this->_baseUrl);
        $min_match_percentage = $this->getService()->evaluateString($this->_minMatchPercentage);
        $search = $this->getService()->evaluateString($this->_search);
        $search_folders = $this->getService()->evaluateString($this->_searchFolders);
        $artwork = $this->getService()->evaluateString($this->_defaultSongImageUrl);
        $background = $this->getService()->evaluateString($this->_backgroundUrl);
        if (!\is_numeric($min_match_percentage)) {
            $min_match_percentage = self::MIN_MATCH_PERCENT;
        }
        if (\is_string($min_match_percentage) && \is_numeric($min_match_percentage)) {
            $min_match_percentage = \intval($min_match_percentage);
        }
        if ($min_match_percentage < 0 || $min_match_percentage > 100) {
            $min_match_percentage = self::MIN_MATCH_PERCENT;
        }
        $model = $this->_getQueryModel();
        $args = ['search' => $search, 'search_folders' => $search_folders, 'orderby' => 'name', 'order' => 'ASC'];
        $args_changed = $args != $model['arguments'];
        if (isset($this->_loadedSongs) && !$args_changed) {
            return new \ArrayIterator($this->_loadedSongs);
        }
        if ($args_changed) {
            $this->_logger->info('Arguments changed. Storing them and rewinding results ...');
            $model['arguments'] = $args;
            $model['post_index'] = 0;
        }
        $this->_logger->info('Scanning dir [' . $base_path . '] against [' . $search . '][' . $search_folders . '] with min match percentage [' . $min_match_percentage . ']');
        $provider = new \Convo\Pckg\Filesystem\Mp3InfoProvider($this->_logger, $base_url, $artwork, $background);
        $filter = new \Convo\Pckg\Filesystem\Mp3Filter($this->_logger, $provider, $min_match_percentage, $search, $search_folders);
        $dir_reader = new \Convo\Pckg\Filesystem\Mp3DirectoryReader($this->_logger, $provider, $filter);
        $this->_loadedSongs = $dir_reader->readFolder($base_path);
        $count = \count($this->_loadedSongs);
        $count_changed = \count($model['playlist']) !== $count;
        $this->_logger->info('Found total [' . $count . '] songs');
        $this->_logger->debug('Contents of the loaded songs [' . \print_r($this->_loadedSongs, \true) . ']');
        if ($count <= 0) {
            $model['playlist'] = [];
        } else {
            if ($args_changed || $count_changed) {
                if ($count_changed) {
                    $this->_logger->warning('Generating playlist because model and query count are different');
                } else {
                    $this->_logger->info('Generating playlist because arguments were changed');
                }
                $model['playlist'] = \range(0, $count - 1);
                if ($model['shuffle_status']) {
                    $this->_logger->info('Shuffling playlist');
                    \shuffle($model['playlist']);
                }
            }
        }
        $this->_logger->debug('Showing the contents of hte query model [' . \print_r($model, \true) . ']');
        $this->_saveQueryModel($model);
        return new \ArrayIterator($this->_loadedSongs);
    }
    // UTIL
    public function __toString()
    {
        return parent::__toString() . '[' . $this->_basePath . '][' . $this->_baseUrl . ']';
    }
}
