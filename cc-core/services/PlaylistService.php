<?php

class PlaylistService extends ServiceAbstract
{
    
    public function getPlaylistVideos(Playlist $playlist)
    {
        $videoIds = Functions::arrayColumn($playlist->entries, 'videoId');
        $videoMapper = new VideoMapper();
        return $videoMapper->getVideosFromList($videoIds);
    }
    
    public function getPlaylistThumbnails(Playlist $playlist)
    {
        $config = Registry::get('config');
        $videoIds = Functions::arrayColumn(array_slice($playlist->entries, 0, 3), 'videoId');
        $videoMapper = new VideoMapper();
        $thumbnailList = array();
        foreach ($videoMapper->getVideosFromList($videoIds) as $video) {
            $thumbnailList[] = $config->thumb_url . '/' . $video->filename . '.jpg';
        }
        return $thumbnailList;
    }
    
    public function addVideoToPlaylist(Video $video, Playlist $playlist)
    {
        $playlistEntryMapper = new PlaylistEntryMapper();
        $playlistEntry = new PlaylistEntry();
        $playlistEntry->playlistId = $playlist->playlistId;
        $playlistEntry->videoId = $video->videoId;
        $playlistEntryMapper->save($playlistEntry);
    }
    
    public function checkListing(Video $video, Playlist $playlist)
    {
        $playlistEntryMapper = new PlaylistEntryMapper();
        return (boolean) $playlistEntryMapper->getPlaylistEntryByCustom(array(
            'playlist_id' => $playlist->playlistId,
            'video_id' => $video->videoId
        ));
    }
    
    public function getPlaylistName(Playlist $playlist)
    {
        switch ($playlist->type) {
            case 'favorites':
                return Language::GetText('favorites');
            case 'watch_later':
                return Language::GetText('watch_later');
            case 'playlist':
                return $playlist->name;
            default:
                throw new Exception('Invalid playlist name');
        }
    }
    
    public function delete(Playlist $playlist)
    {
        // Delete all playlist entries
        $playlistEntryMapper = new PlaylistEntryMapper();
        foreach ($playlist->entries as $playlistEntry) {
            $playlistEntryMapper->delete($playlistEntry->playlistEntryId);
        }
        
        // Delete playlist
        $playlistMapper = $this->_getMapper();
        $playlistMapper->delete($playlist->playlistId);
    }
    
    public function deleteVideo(Video $video, Playlist $playlist)
    {
        $playlistEntryMapper = new PlaylistEntryMapper();
        $playlistEntry = $playlistEntryMapper->getPlaylistEntryByCustom(array(
            'playlist_id' => $playlist->playlistId,
            'video_id' => $video->videoId
        ));      
        $playlistEntryMapper->delete($playlistEntry->playlistEntryId);
        unset($playlist->entries[$playlistEntry->playlistEntryId]);
        return $playlist;
    }
    
    /**
     * Retrieve instance of Playlist mapper
     * @return PlaylistMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new PlaylistMapper();
    }
}