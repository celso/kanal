<?php
/**
 *
 *
 * @author Celso Martinho
 */

class Kanal {

    function __construct( $clientId, $clientSecret, $accessToken ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
        $this->tools=new Kanal_Tools;
        $this->list_videos_page_limit=100;
        $this->api_path="https://services.sapo.pt/IPTV/MEO/Kanal/api/";
    }

    function channelDetails( $chan ) {
        $r=$this->tools->post( $this->api_path."channels/".$chan."?access_token=".$this->accessToken );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                return $r->data->channels[0];
            }
        }
        return false;
    }

    function searchChannels( $query, $offset = 0, $limit = 15 ) {
        $r=$this->tools->post( $this->api_path."channels?q=".$query."&offset=".$offset."&limit=".$limit."?access_token=".$this->accessToken );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                return $r->data->channels;
            }
        }
        return false;
    }

    function listChannels() {
        $r=$this->tools->post( $this->api_path."channels?access_token=".$this->accessToken );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                return $r->data->channels;
            }
        }
        return false;
    }

    function listSchedule( $chan ) {
        $r=$this->tools->post( $this->api_path."channels/".$chan."/schedule?access_token=".$this->accessToken );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                return $r->data;
            }
        }
        return false;
    }

    function setSchedule( $chan, $videos ) {
        $args="access_token=".$this->accessToken;
        $args.="&channel_id=".$chan;
        foreach ( $videos as $v ) {
            $args.="&video_id[]=".$v;
        }
        $r=$this->tools->post( $this->api_path."channels/".$chan."/schedule", $args );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                return  $r->data;
            }
        }
        return false;
    }

    function listVideos( $offset=0, $limit=1000 ) {
        $videos=array();
        for ( $p=0;$p<( $limit/$this->list_videos_page_limit );$p++ ) {
            $r=$this->tools->post( $this->api_path."videos?offset=".( $p*$this->list_videos_page_limit )."&limit=".$limit."&access_token=".$this->accessToken );
            if ( $this->tools->last_code!=401 ) {
                $a=json_decode( $r );
                if ( $a->data->items_in_response==0 ) break;
                $videos=array_merge( $videos, $a->data->videos );
            }
            return $videos;
        }
        return false;
    }

    function uploadVideo( $file, $title, $chan_id=0, $verbose=true ) {
        $r=$this->tools->post( $this->api_path."videos/prepare_upload", "title=".rawurlencode( $title )."&channel_id=".$chan_id."&access_token=".$this->accessToken );
        if ( $this->tools->last_code!=401 ) {
            $r=json_decode( $r );
            if ( $r->success==1 ) {
                $args = "token=".$r->data->token."&content_file[]=@".$file."&redir".rawurlencode( $r->data->redir );
                $data=array();
                $data['content_file']="@".$file;
                $data['token']=$r->data->token;
                $data['redir']=$r->data->redir;
                $this->tools->http_timeout=3600;
                $r=$this->tools->post( "http://uploader.nexttv.sapo.pt/upload_token.html", $data, $verbose );
                if ( $this->tools->last_code!=401 ) {
                    $r=json_decode( $r );
                    if ( $r->success==1 ) {
                        return $r->data;
                    }
                }
            }
        }
        return false;
    }

}

    class Kanal_Tools {
        var $http_timeout=10;
        var $last_cookies=NULL;
        var $last_code;

        function __construct() {
            $this->agent="Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14";
        }

        function post( $url, $args=array(), $progress=false ) {
            // echo "Posting to $url\n";
            $this->previousProgress = 0;

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_USERAGENT, $this->agent );
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_FAILONERROR, false );

            if ( $progress ) {
                curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
                curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, 'Kanal_Tools::progressCallback' );
            }

            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->http_timeout );
            curl_setopt( $ch, CURLOPT_TIMEOUT, $this->http_timeout );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
            if ( sizeof( $args ) ) {
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $args );
            }
            curl_setopt( $ch, CURLOPT_VERBOSE, false );
            $r=curl_exec( $ch );
            $this->last_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            if ( $this->last_code==200||$this->last_code==302 ) {
                return $r;
            }
            return false;
        }


        function progressCallback( $download_size, $downloaded_size, $upload_size, $uploaded_size ) {
            if ( $upload_size == 0 )
                $progress = 0;
            else
                $progress = round( $uploaded_size * 100 / $upload_size );

            if ( $progress > $this->previousProgress ) {
                $this->previousProgress = $progress;
                echo $progress."% ";
            }
        }

    }



    
