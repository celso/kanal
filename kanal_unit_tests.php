#!/usr/bin/php -q
<?php
/*
 * This is a PHP command line script to test the Meo Kanal PHP class
 */

require_once "classes/kanal.php";

set_time_limit( 0 );
error_reporting( 1 );

define( RC_KEYS_FILE, getenv('HOME').'/.kanal_keys' );

if ( @file_exists( RC_KEYS_FILE ) ) {
    echo "Using local keys file\n";
    require_once RC_KEYS_FILE;
}
else {
    // put your keys here (or use a keys file, see above)
    // check the get_keys.php script to learn how to obtain the API keys
    echo "Using script keys\n";
    $clientId = '111111111111111111111111111111111111111111111111111111';
    $clientSecret = '111111111111111111111111111111111111111111111111111111';
    $accessToken = '111111111111111111111111111111111111111111111111111111';
}

$kanal=new Kanal( $clientId, $clientSecret, $accessToken );

// Get all of video collection, from offset 0 to 10000
// http://kanal.pt/developer/console#get_videos_doc

echo " * running listVideos\n";
$r=$kanal->listVideos( 0, 10000 );
kResponse($r);

# http://kanal.pt/developer/console#get_channels_doc
echo " * running listChannels\n";
$r=$kanal->listChannels();
kResponse($r);
$channel_id=$r[0]->channel_id;

# http://kanal.pt/developer/console#get_schedule_doc
echo " * running listSchedule\n";
$r=$kanal->listSchedule( $channel_id );
kResponse($r,$r->videos);

# http://kanal.pt/developer/console#create_schedule_doc
echo " * running setSchedule\n";
$videos=array();
// randomize videos
foreach ( $r->videos as $v ) array_push( $videos, $v->video_id );
shuffle($videos);
$r=$kanal->setSchedule( $channel_id, $videos );
kResponse($r,$r->videos);

# http://kanal.pt/developer/console#upload_video_doc
echo " * running uploadVideos\n";
$r=$kanal->uploadVideo( "files/cc.mp4", 'Creative Commons mp4 test', $channel_id, true );
echo "\n";
kResponse($r);

exit;


function kResponse($r,$obj=false) {
    if($r==false) {
        echo "   * failed. Wrong keys?\n";
        exit;
    }
    else
    {
        echo "   * got ".($obj?sizeof($obj):sizeof($r))." records\n";
    }
}

exit;
