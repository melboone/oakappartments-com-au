<?php
require_once( dirname(__FILE__).'/form.lib.php' );

define( 'PHPFMG_USER', "sales@oakapartments.com.au" ); // must be a email address. for sending password to you.
define( 'PHPFMG_PW', "72bf31" );    

?>
<?php
/**
 * GNU Library or Lesser General Public License version 2.0 (LGPLv2)
*/

# main
# ------------------------------------------------------
error_reporting( E_ERROR ) ;
phpfmg_admin_main();
# ------------------------------------------------------




function phpfmg_admin_main(){
    $mod  = isset($_REQUEST['mod'])  ? $_REQUEST['mod']  : '';
    $func = isset($_REQUEST['func']) ? $_REQUEST['func'] : '';
    $function = "phpfmg_{$mod}_{$func}";
    if( !function_exists($function) ){
        phpfmg_admin_default();
        exit;
    };

    // no login required modules
    $public_modules   = false !== strpos('|captcha|', "|{$mod}|", "|ajax|");
    $public_functions = false !== strpos('|phpfmg_ajax_submit||phpfmg_mail_request_password||phpfmg_filman_download||phpfmg_image_processing||phpfmg_dd_lookup|', "|{$function}|") ;   
    if( $public_modules || $public_functions ) { 
        $function();
        exit;
    };
    
    return phpfmg_user_isLogin() ? $function() : phpfmg_admin_default();
}

function phpfmg_ajax_submit(){
    $phpfmg_send = phpfmg_sendmail( $GLOBALS['form_mail'] );
    $isHideForm  = isset($phpfmg_send['isHideForm']) ? $phpfmg_send['isHideForm'] : false;

    $response = array(
        'ok' => $isHideForm,
        'error_fields' => isset($phpfmg_send['error']) ? $phpfmg_send['error']['fields'] : '',
        'OneEntry' => isset($GLOBALS['OneEntry']) ? $GLOBALS['OneEntry'] : '',
    );
    
    @header("Content-Type:text/html; charset=$charset");
    echo "<html><body><script>
    var response = " . json_encode( $response ) . ";
    try{
        parent.fmgHandler.onResponse( response );
    }catch(E){};
    \n\n";
    echo "\n\n</script></body></html>";

}


function phpfmg_admin_default(){
    if( phpfmg_user_login() ){
        phpfmg_admin_panel();
    };
}



function phpfmg_admin_panel()
{    
    phpfmg_admin_header();
    phpfmg_writable_check();
?>    
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign=top style="padding-left:280px;">

<style type="text/css">
    .fmg_title{
        font-size: 16px;
        font-weight: bold;
        padding: 10px;
    }
    
    .fmg_sep{
        width:32px;
    }
    
    .fmg_text{
        line-height: 150%;
        vertical-align: top;
        padding-left:28px;
    }

</style>

<script type="text/javascript">
    function deleteAll(n){
        if( confirm("Are you sure you want to delete?" ) ){
            location.href = "admin.php?mod=log&func=delete&file=" + n ;
        };
        return false ;
    }
</script>


<div class="fmg_title">
    1. Email Traffics
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=1">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=1">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_EMAILS_LOGFILE) ){
            echo '<a href="#" onclick="return deleteAll(1);">delete all</a>';
        };
    ?>
</div>


<div class="fmg_title">
    2. Form Data
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=2">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=2">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_SAVE_FILE) ){
            echo '<a href="#" onclick="return deleteAll(2);">delete all</a>';
        };
    ?>
</div>

<div class="fmg_title" style="display:none">
    3. Form Generator
</div>
<div class="fmg_text" style="display:none">
    <a href="http://www.formmail-maker.com/generator.php" onclick="document.frmFormMail.submit(); return false;" title="<?php echo htmlspecialchars(PHPFMG_SUBJECT);?>">Edit Form</a> &nbsp;&nbsp;
    <a href="http://www.formmail-maker.com/generator.php" >New Form</a>
</div style="display:none">
    <form name="frmFormMail" action='http://www.formmail-maker.com/generator.php' method='post' enctype='multipart/form-data'>
    <input type="hidden" name="uuid" value="<?php echo PHPFMG_ID; ?>">
    <input type="hidden" name="external_ini" value="<?php echo function_exists('phpfmg_formini') ?  phpfmg_formini() : ""; ?>">
    </form>

		</td>
	</tr>
</table>

<?php
    phpfmg_admin_footer();
}



function phpfmg_admin_header( $title = '' ){
    header( "Content-Type: text/html; charset=" . PHPFMG_CHARSET );
?>
<html>
<head>
    <title><?php echo '' == $title ? '' : $title . ' | ' ; ?>PHP FormMail Admin Panel </title>

    <style type='text/css'>
    body, td, label, div, span{
        font-family : Verdana, Arial, Helvetica, sans-serif;
        font-size : 12px;
    }
    </style>
</head>
<body  marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<table cellspacing=0 cellpadding=0 border=0 width="100%">
    <td nowrap align=center style="background-color:#024e7b;padding:10px;font-size:18px;color:#ffffff;font-weight:bold;width:250px;" >
        Form Admin Panel
    </td>
    <td style="padding-left:30px;background-color:#86BC1B;width:100%;font-weight:bold;" >
        &nbsp;
<?php
    if( phpfmg_user_isLogin() ){
        echo '<a href="admin.php" style="color:#ffffff;">Main Menu</a> &nbsp;&nbsp;' ;
        echo '<a href="admin.php?mod=user&func=logout" style="color:#ffffff;">Logout</a>' ;
    }; 
?>
    </td>
</table>

<div style="padding-top:28px;">

<?php
    
}


function phpfmg_admin_footer(){
?>

</div>

</body>
</html>
<?php
}


function phpfmg_image_processing(){
    $img = new phpfmgImage();
    $img->out_processing_gif();
}


# phpfmg module : captcha
# ------------------------------------------------------
function phpfmg_captcha_get(){
    $img = new phpfmgImage();
    $img->out();
    //$_SESSION[PHPFMG_ID.'fmgCaptchCode'] = $img->text ;
    $_SESSION[ phpfmg_captcha_name() ] = $img->text ;
}



function phpfmg_captcha_generate_images(){
    for( $i = 0; $i < 50; $i ++ ){
        $file = "$i.png";
        $img = new phpfmgImage();
        $img->out($file);
        $data = base64_encode( file_get_contents($file) );
        echo "'{$img->text}' => '{$data}',\n" ;
        unlink( $file );
    };
}


function phpfmg_dd_lookup(){
    $paraOk = ( isset($_REQUEST['n']) && isset($_REQUEST['lookup']) && isset($_REQUEST['field_name']) );
    if( !$paraOk )
        return;
        
    $base64 = phpfmg_dependent_dropdown_data();
    $data = @unserialize( base64_decode($base64) );
    if( !is_array($data) ){
        return ;
    };
    
    
    foreach( $data as $field ){
        if( $field['name'] == $_REQUEST['field_name'] ){
            $nColumn = intval($_REQUEST['n']);
            $lookup  = $_REQUEST['lookup']; // $lookup is an array
            $dd      = new DependantDropdown(); 
            echo $dd->lookupFieldColumn( $field, $nColumn, $lookup );
            return;
        };
    };
    
    return;
}


function phpfmg_filman_download(){
    if( !isset($_REQUEST['filelink']) )
        return ;
        
    $info =  @unserialize(base64_decode($_REQUEST['filelink']));
    if( !isset($info['recordID']) ){
        return ;
    };
    
    $file = PHPFMG_SAVE_ATTACHMENTS_DIR . $info['recordID'] . '-' . $info['filename'];
    phpfmg_util_download( $file, $info['filename'] );
}


class phpfmgDataManager
{
    var $dataFile = '';
    var $columns = '';
    var $records = '';
    
    function phpfmgDataManager(){
        $this->dataFile = PHPFMG_SAVE_FILE; 
    }
    
    function parseFile(){
        $fp = @fopen($this->dataFile, 'rb');
        if( !$fp ) return false;
        
        $i = 0 ;
        $phpExitLine = 1; // first line is php code
        $colsLine = 2 ; // second line is column headers
        $this->columns = array();
        $this->records = array();
        $sep = chr(0x09);
        while( !feof($fp) ) { 
            $line = fgets($fp);
            $line = trim($line);
            if( empty($line) ) continue;
            $line = $this->line2display($line);
            $i ++ ;
            switch( $i ){
                case $phpExitLine:
                    continue;
                    break;
                case $colsLine :
                    $this->columns = explode($sep,$line);
                    break;
                default:
                    $this->records[] = explode( $sep, phpfmg_data2record( $line, false ) );
            };
        }; 
        fclose ($fp);
    }
    
    function displayRecords(){
        $this->parseFile();
        echo "<table border=1 style='width=95%;border-collapse: collapse;border-color:#cccccc;' >";
        echo "<tr><td>&nbsp;</td><td><b>" . join( "</b></td><td>&nbsp;<b>", $this->columns ) . "</b></td></tr>\n";
        $i = 1;
        foreach( $this->records as $r ){
            echo "<tr><td align=right>{$i}&nbsp;</td><td>" . join( "</td><td>&nbsp;", $r ) . "</td></tr>\n";
            $i++;
        };
        echo "</table>\n";
    }
    
    function line2display( $line ){
        $line = str_replace( array('"' . chr(0x09) . '"', '""'),  array(chr(0x09),'"'),  $line );
        $line = substr( $line, 1, -1 ); // chop first " and last "
        return $line;
    }
    
}
# end of class



# ------------------------------------------------------
class phpfmgImage
{
    var $im = null;
    var $width = 73 ;
    var $height = 33 ;
    var $text = '' ; 
    var $line_distance = 8;
    var $text_len = 4 ;

    function phpfmgImage( $text = '', $len = 4 ){
        $this->text_len = $len ;
        $this->text = '' == $text ? $this->uniqid( $this->text_len ) : $text ;
        $this->text = strtoupper( substr( $this->text, 0, $this->text_len ) );
    }
    
    function create(){
        $this->im = imagecreate( $this->width, $this->height );
        $bgcolor   = imagecolorallocate($this->im, 255, 255, 255);
        $textcolor = imagecolorallocate($this->im, 0, 0, 0);
        $this->drawLines();
        imagestring($this->im, 5, 20, 9, $this->text, $textcolor);
    }
    
    function drawLines(){
        $linecolor = imagecolorallocate($this->im, 210, 210, 210);
    
        //vertical lines
        for($x = 0; $x < $this->width; $x += $this->line_distance) {
          imageline($this->im, $x, 0, $x, $this->height, $linecolor);
        };
    
        //horizontal lines
        for($y = 0; $y < $this->height; $y += $this->line_distance) {
          imageline($this->im, 0, $y, $this->width, $y, $linecolor);
        };
    }
    
    function out( $filename = '' ){
        if( function_exists('imageline') ){
            $this->create();
            if( '' == $filename ) header("Content-type: image/png");
            ( '' == $filename ) ? imagepng( $this->im ) : imagepng( $this->im, $filename );
            imagedestroy( $this->im ); 
        }else{
            $this->out_predefined_image(); 
        };
    }

    function uniqid( $len = 0 ){
        $md5 = md5( uniqid(rand()) );
        return $len > 0 ? substr($md5,0,$len) : $md5 ;
    }
    
    function out_predefined_image(){
        header("Content-type: image/png");
        $data = $this->getImage(); 
        echo base64_decode($data);
    }
    
    // Use predefined captcha random images if web server doens't have GD graphics library installed  
    function getImage(){
        $images = array(
			'1E18' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7GB1EQxmmMEx1QBJjdRBpYAhhCAhAEhMFijGGMIJkkPQCeVPg6sBOWpk1NWzVtFVTs5Dch6YOSQybeXjtgLglRDSUMdQBxc0DFX5UhFjcBwBlash7kHIMgQAAAABJRU5ErkJggg==',
			'7D36' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7QkNFQxhDGaY6IIu2irSyNjoEBKCKNTo0BDoIIItNAYo1OjqguC9q2sqsqStTs5Dcx+gAVodiHmsDxDwRJDERLGIBDZhuCWjA4uYBCj8qQizuAwCDOc1lXa9YJQAAAABJRU5ErkJggg==',
			'4AC9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpI37pjAEMIQ6THVAFgthDGF0CAgIQBJjDGFtZW0QdBBBEmOdItLo2sAIEwM7adq0aStTV62KCkNyXwBYHcNUZL2hoaKhQLEGERS3gNQJOKCLOaK5BSTmgO7mgQo/6kEs7gMA7RjMa3cMMwkAAAAASUVORK5CYII=',
			'6B98' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WANEQxhCGaY6IImJTBFpZXR0CAhAEgtoEWl0bQh0EEEWaxBpZW0IgKkDOykyamrYysyoqVlI7gsBmscQEoBqXqtIowO6eUAxRzQxbG7B5uaBCj8qQizuAwAlLcz/9KKjEwAAAABJRU5ErkJggg==',
			'CCE0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAW0lEQVR4nGNYhQEaGAYTpIn7WEMYQ1lDHVqRxURaWRtdGximOiCJBTSKNADFAgKQxRpEGlgbGB1EkNwXtWraqqWhK7OmIbkPTR1uMSx2YHMLNjcPVPhREWJxHwCmcMyON/TJEAAAAABJRU5ErkJggg==',
			'5D75' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7QkNEQ1hDA0MDkMQCGkRaGRoCHRhQxRod0MQCA4BijY6uDkjuC5s2bWXW0pVRUcjuawWqm8LQIIJsM0gsAFUsACjm6MDogCwmMkWklbWBIQDZfawBQDc3MEx1GAThR0WIxX0AD6PMr8JV+lIAAAAASUVORK5CYII=',
			'D22D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7QgMYQxhCGUMdkMQCprC2Mjo6OgQgi7WKNLo2BDqIoIgxNDogxMBOilq6aumqlZlZ05DcB1Q3haGVEV1vAMMUdDFGB4YANLEprA2MDowobgkNEA11DQ1EcfNAhR8VIRb3AQBHBcwgk07z1wAAAABJRU5ErkJggg==',
			'26DE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDGUMDkMREprC2sjY6OiCrC2gVaWRtCEQRY2gVaUASg7hp2rSwpasiQ7OQ3Rcg2oqul9FBpNEVTYy1AVMMaAOGW0JDMd08UOFHRYjFfQCbZsowBbT9iQAAAABJRU5ErkJggg==',
			'35D8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7RANEQ1lDGaY6IIkFTBFpYG10CAhAVtkKFGsIdBBBFpsiEsLaEABTB3bSyqipS5euipqahey+KQyNrgh1UPNAYmjmtYpgiAVMYW1Fd4toAGMIupsHKvyoCLG4DwBv5s07n/JVlAAAAABJRU5ErkJggg==',
			'E3FF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAUklEQVR4nGNYhQEaGAYTpIn7QkNYQ1hDA0NDkMQCGkRaWRsYHRhQxBgaXTHFkNWBnRQatSpsaejK0Cwk96Gpw2ceFjFMt4DdjCY2UOFHRYjFfQC8AcoT+w3l0wAAAABJRU5ErkJggg==',
			'8461' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7WAMYWhlCgRhJTGQKw1RGR4epyGIBQFWsDQ6hqOoYXVkb4HrBTloatXTp0qmrliK7T2SKSCuro0Mrqnmioa4gU1HtaGVFEwO6pZURTS/UzaEBgyD8qAixuA8ArDnL7OdG9gwAAAAASUVORK5CYII=',
			'6C4D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WAMYQxkaHUMdkMREprA2OrQ6OgQgiQW0iDQ4THV0EEEWawDyAuFiYCdFRk1btTIzM2sakvtCpog0sDai6W0FioUGYog5oKkDu6UR1S3Y3DxQ4UdFiMV9AE8WzRvTrnA3AAAAAElFTkSuQmCC',
			'1FB9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7GB1EQ11DGaY6IImxOog0sDY6BAQgiYmCxBoCgSSyXpA6R5gY2Ekrs6aGLQ1dFRWG5D6IOoepGHobAhqwiGGxA80tIUAxNDcPVPhREWJxHwAu/8ngntEHjgAAAABJRU5ErkJggg==',
			'1905' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7GB0YQximMIYGIImxOrC2MoQyOiCrE3UQaXR0dHRA1SvS6NoQ6OqA5L6VWUuXpq6KjIpCch/QjkDXhoAGERS9DI2YYixgO1DFQG5hCEB2n2gIyM0MUx0GQfhREWJxHwCkU8jCHJrkHQAAAABJRU5ErkJggg==',
			'B5A5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nM2QsQ2AMAwEnSIbmH1CQf+RcEGmMQUbmBFoMiUpHaAECX93etknU72N0p/yiZ9gELIgcAzGShKS72FjDePYM+M5ap6S85OyH0ddSnF+MFonhXK3rzG5Mm69nDpmcYsKeD9BaHexpx/878U8+J3eT833NZHjhgAAAABJRU5ErkJggg==',
			'7052' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nM2Quw2AMAxE7cIbZCBT0LuIm/QUMIVTZANgBzIln8oISpDi6550uidDfZxBS/nFTxWElBf2tGAkA5Ebo0KGHDybQ+4XsOD90roN41ST8ztamU2y3yC7WPEuwc4NmT0Tw4gdy52BgKLGBv73YV78dnCOy6W3QqvLAAAAAElFTkSuQmCC',
			'62D6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDGaY6IImJTGFtZW10CAhAEgtoEWl0bQh0EEAWa2AAiyG7LzJq1dKlqyJTs5DcFzKFYQprQyCqea0MAUAxBxEUMUYHdDGgWxrQ3cIaIBrqiubmgQo/KkIs7gMAGNrM7eEoxSkAAAAASUVORK5CYII=',
			'A965' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nGNYhQEaGAYTpIn7GB0YQxhCGUMDkMRYA1hbGR0dHZDViUwRaXRtQBULaAWJMbo6ILkvaunSpalTV0ZFIbkvoJUx0NXRoUEESW9oKANQbwCKWEArC1As0AFVDOQWh4AAFDGQmxmmOgyC8KMixOI+ALL+zESrMYggAAAAAElFTkSuQmCC',
			'E4B3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMYWllDGUIdkMQCGhimsjY6OgSgioWyAkkRFDFGV9ZGh4YAJPeFRi1dujR01dIsJPcBdbUiqYOKiYa6YpgHdAs2MTS3YHPzQIUfFSEW9wEA7JPOc5SP4S4AAAAASUVORK5CYII=',
			'85D1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WANEQ1lDGVqRxUSmiDSwNjpMRRYLaAWKNQSEoqkLAYrB9IKdtDRq6tKlq6KWIrtPZApDoytCHdQ8bGIiGGIiU1hbgW5BEWMNYAwBujk0YBCEHxUhFvcBALrvzYtl+boZAAAAAElFTkSuQmCC',
			'AB62' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB1EQxhCGaY6IImxBoi0Mjo6BAQgiYlMEWl0bXB0EEESC2gVaWUFySG5L2rp1LClU4E0kvvA6hwdGpHtCA0FmRfQyoBqHkhsCpoY2C2oYiA3M4aGDILwoyLE4j4AJm7NPesxrDcAAAAASUVORK5CYII=',
			'C307' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WENYQximMIaGIImJtIq0MoQCaSSxgEaGRkdHB1SxBoZWViAZgOS+qFWrwpauilqZheQ+qLpWBlS9ja4NAVMYMO0IYMBwC6MDFjejiA1U+FERYnEfAFDDzBS/2oKGAAAAAElFTkSuQmCC',
			'CE47' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WENEQxkaHUNDkMREWkUaGFodGkSQxAIagbypaGIgXqADkEa4L2rV1LCVmVkrs5DcB1LH2ujQyoCmlzU0YAoDuh2NDgEM6G5pdHTA4mYUsYEKPypCLO4DADrRzMt9rY1PAAAAAElFTkSuQmCC',
			'A1A3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7GB0YAhimMIQ6IImxBjAGMIQyOgQgiYlMAYo6OjSIIIkFtDIEsDYENAQguS9qKQRlIbkPTR0YhoYCxUIDsJqHKRaI4paAVtZQoDoUNw9U+FERYnEfAB+Qy90NaOj3AAAAAElFTkSuQmCC',
			'F413' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMZWhmmMIQ6IIkFNDBMZQhhdAhAFQtlDGFoEEERY3QF6m0IQHJfaNTSpaumrVqaheS+gAaRViR1UDHRUIcp6OaB3YJFDMMtrYyhDihuHqjwoyLE4j4AWuPNTZZuerAAAAAASUVORK5CYII=',
			'287F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDA0NDkMREprC2MjQEOiCrC2gVaXRAE2NoBaprdISJQdw0bWXYqqUrQ7OQ3RcAVDeFEUUvowPQvABUMdYGEaBpqGIiDaytrA2oYqGhQDejiQ1U+FERYnEfANdAySmIPKx0AAAAAElFTkSuQmCC',
			'08F3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDA0IdkMRYA1hbWYEyAUhiIlNEGl1BNJJYQCtIHZBGcl/U0pVhS0NXLc1Cch+aOqgYpnnY7MDmFrCbGxhQ3DxQ4UdFiMV9AKVmy8Jg0PRHAAAAAElFTkSuQmCC',
			'5040' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7QkMYAhgaHVqRxQIaGEMYWh2mOqCIsbYyTHUICEASCwwQaXQIdHQQQXJf2LRpKzMzM7OmIbuvVaTRtRGuDiEWGogiFtAKtKMR1Q6RKUC3NKK6hTUA080DFX5UhFjcBwA1QczmdfLF/gAAAABJRU5ErkJggg==',
			'8883' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWElEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGUIdkMREprC2Mjo6OgQgiQW0ijS6NgQ0iGCoc2gIQHLf0qiVYatCVy3NQnIfmjqc5uG2A9Ut2Nw8UOFHRYjFfQAK1sz0o5HG4AAAAABJRU5ErkJggg==',
			'B388' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QgNYQxhCGaY6IIkFTBFpZXR0CAhAFmtlaHRtCHQQQVHHgKwO7KTQqFVhq0JXTc1Cch+aOtzmYbUD0y3Y3DxQ4UdFiMV9AM5WzYbF7kBEAAAAAElFTkSuQmCC',
			'0A04' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7GB0YAhimMDQEIImxBjCGMIQyNCKLiUxhbWV0dGhFFgtoFWl0bQiYEoDkvqil01amroqKikJyH0RdoAOqXtFQoFhoCIodIo2Ojg5obhFpdAhFdR+jA1AMzc0DFX5UhFjcBwAE1s39E/YHyAAAAABJRU5ErkJggg==',
			'0A69' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nGNYhQEaGAYTpIn7GB0YAhhCGaY6IImxBjCGMDo6BAQgiYlMYW1lbXB0EEESC2gVaXQFmiCC5L6opdNWpk5dFRWG5D6wOkeHqah6RUNdGwIaRFDsAJkXgGIHa4BIoyOaW4A2NjqguXmgwo+KEIv7AKg1zCfJX+sNAAAAAElFTkSuQmCC',
			'00A7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7GB0YAhimMIaGIImxBjCGMIQyNIggiYlMYW1ldHRAEQtoFWl0bQgAQoT7opZOW5m6KmplFpL7oOpaGdD1hgZMYUCzg7UhIIABzS2sDYEO6G5GFxuo8KMixOI+AILsy6iZubOJAAAAAElFTkSuQmCC',
			'E69E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGUMDkMQCGlhbGR0dHRhQxEQaWRsC0cUakMTATgqNmha2MjMyNAvJfQENoq0MIRh6Gx0wzWt0xBDDdAs2Nw9U+FERYnEfAIavyu1mrs9TAAAAAElFTkSuQmCC',
			'09B0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGVqRxVgDWFtZGx2mOiCJiUwRaXRtCAgIQBILaAWKNTo6iCC5L2rp0qWpoSuzpiG5L6CVMRBJHVSMAWheIIqYyBQWDDuwuQWbmwcq/KgIsbgPAMi0zLBc+j+LAAAAAElFTkSuQmCC',
			'19F4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDAxoCkMRYHVhbWRsYGpHFRB1EGl0bGFoDUPSCxaYEILlvZdbSpamhq6KikNwHtCPQFUii6mUA6mUMDUERYwGZ14CqDuwWFDHREKCb0cQGKvyoCLG4DwA6+Mqgp2Fw5AAAAABJRU5ErkJggg==',
			'CD48' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7WENEQxgaHaY6IImJtIq0MrQ6BAQgiQU0igBVOTqIIIs1AMUC4erATopaNW1lZmbW1Cwk94HUuTaimQcSCw1ENQ9kRyOqHWC3oOnF5uaBCj8qQizuAwBRD860KN7dNAAAAABJRU5ErkJggg==',
			'BC24' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7QgMYQxlCGRoCkMQCprA2Ojo6NKKItYo0uAJJVHUiYDIAyX2hUdNWrVqZFRWF5D6wulZGB3TzGKYwhoagiTkEYHGLA6oYyM2soQEoYgMVflSEWNwHAFrIz3WHRINyAAAAAElFTkSuQmCC',
			'1DFA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7GB1EQ1hDA1qRxVgdRFpZGximOiCJiTqINLo2MAQEoOgFiQFJJPetzJq2MjUUSCK5D00dslhoCG7zYGJAt6CKiYYA3YwmNlDhR0WIxX0ANArI1TK26EEAAAAASUVORK5CYII=',
			'48BB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpI37pjCGsIYyhjogi4WwtrI2OjoEIIkxhog0ujYEOoggibFOQVEHdtK0aSvDloauDM1Ccl/AFEzzQkMxzWOYgk0MUy9WNw9U+FEPYnEfAMhIzANI6bO+AAAAAElFTkSuQmCC',
			'1450' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7GB0YWllDHVqRxVgdGKayNjBMdUASE3VgCAWKBQSg6GV0ZZ3K6CCC5L6VWUuXLs3MzJqG5D6gCqD5gTB1UDHRUAcMMaBbGgLQ7GBoZXR0QHVLCEMrQygDipsHKvyoCLG4DwCpkcis9O7yvAAAAABJRU5ErkJggg==',
			'A492' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nM2QvQ2AQAiFH8VtcO6DhT0m0twGboEFG+gIV+iUWuJPqYm87nsJfAHbbQx/yid+xHAoFg4sCRZqWSSwPEOT9ZwDE6cumVgOfqXWuo5lK8FPPDsGmeIN1Ub5aHDaByeT+cYOlyuDkg4/+N+LefDbASD1zGvfPzK9AAAAAElFTkSuQmCC',
			'649C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYWhlCGaYGIImJTGGYyujoECCCJBbQwhDK2hDowIIs1sDoChJDdl9k1NKlKzMjs5DdFzJFpJUhBK4OordVNNShAV2MoZURzQ6gW1rR3YLNzQMVflSEWNwHAJg+yvo7u8udAAAAAElFTkSuQmCC',
			'083C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7GB0YQxhDGaYGIImxBrC2sjY6BIggiYlMEWl0aAh0YEESC2hlbWVodHRAdl/U0pVhq6auzEJ2H5o6qBjEPAYCdmBzCzY3D1T4URFicR8AZpbLmhJPBRAAAAAASUVORK5CYII=',
			'4EA9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpI37poiGMkxhmOqALBYi0sAQyhAQgCTGCBRjdHR0EEESY50i0sDaEAgTAztp2rSpYUtXRUWFIbkvAKwuYCqy3tBQoFhoQIMIilvA6hywiKG4BeRmkHkobh6o8KMexOI+AJ19zB4UgVRJAAAAAElFTkSuQmCC',
			'7612' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nM2QMQ6AMAgA6dAf4H/qDzoUB19DB36AfURfKcaFRkdNCgPhAuEC9EcwzJS/+BGFAgpH8lSiQIGcB4Y1lJDQM7VOgdH77W3rzYrzC2kRm6v+RmSsSUG8C95MPcscr908MjOhlcoE//swX/xOFUzLf2rCsgoAAAAASUVORK5CYII=',
			'73E1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QkNZQ1hDHVpRRFtFWlkbGKaiijE0ujYwhKKITWEAqYPphbgpalXY0tBVS5Hdx+iAog4MgXyQeShiIljEAhpEMPQGNIDdHBowCMKPihCL+wDEcMs5Ztp/igAAAABJRU5ErkJggg==',
			'95E3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WANEQ1lDHUIdkMREpog0sDYwOgQgiQW0gsSAcqhiISCxACT3TZs6denS0FVLs5Dcx+rK0OiKUAeBrRAxZPMEWkUwxESmsLaiu4U1gDEE3c0DFX5UhFjcBwAf/cwa8gSL7gAAAABJRU5ErkJggg==',
			'1F82' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7GB1EQx1CGaY6IImxOog0MDo6BAQgiYkCxVgbAoEksl6wugYRJPetzJoatip01aooJPdB1TU6oOllbQhoZcAUm4IuBnILsphoCNDGUMbQkEEQflSEWNwHAGzdySz8hW7cAAAAAElFTkSuQmCC',
			'1788' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7GB1EQx1CGaY6IImxOjA0Ojo6BAQgiYkCxVwbAh1EUPQytDIi1IGdtDJr1bRVoaumZiG5D6gugBHNPEYHRgdWDPNYGzDFRBrQ9YqGAFWguXmgwo+KEIv7AGfNyRjXVzIpAAAAAElFTkSuQmCC',
			'60BE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGUMDkMREpjCGsDY6OiCrC2hhbWVtCEQVaxBpdEWoAzspMmraytTQlaFZSO4LmYKiDqK3FSiGbl4rph3Y3ILNzQMVflSEWNwHAGTRysositwpAAAAAElFTkSuQmCC',
			'4D8A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpI37poiGMIQytKKIhYi0Mjo6THVAEmMMEWl0bQgICEASY50i0ujo6OggguS+adOmrcwKXZk1Dcl9AajqwDA0FGReYGgIilvAYijqgGJAtziiiYHczIgqNlDhRz2IxX0A4QXL6Zn7UrwAAAAASUVORK5CYII=',
			'A6CC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7GB0YQxhCHaYGIImxBrC2MjoEBIggiYlMEWlkbRB0YEESC2gVaWAFmoDsvqil08KWrlqZhey+gFbRViR1YBgaKtLoiiYGNA8ohm4HplsCWjHdPFDhR0WIxX0Aw8bLYq0z2NQAAAAASUVORK5CYII=',
			'5981' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGVqRxQIaWFsZHR2mooqJNLo2BIQiiwUGiDQ6OjrA9IKdFDZt6dKs0FVLUdzXyhiIpA4qxgAyD9XeVhYMMZEpYLegiLEGgN0cGjAIwo+KEIv7AH6FzF3zTYgwAAAAAElFTkSuQmCC',
			'B42E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QgMYWhlCGUMDkMQCpjBMZXR0dEBWFwBUxdoQiCo2hdGVASEGdlJo1NKlq1ZmhmYhuS9gikgrQysjmnmioQ5T0MWAbglAE5sC0okqBnIza2ggipsHKvyoCLG4DwAjAMqC9eiKLQAAAABJRU5ErkJggg==',
			'2399' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7WANYQxhCGaY6IImJTBFpZXR0CAhAEgtoZWh0bQh0EEHW3crQyooQg7hp2qqwlZlRUWHI7gsAqgwJmIqsl9GBodGhIaABWYy1gaHRsSEAxQ6RBky3hIZiunmgwo+KEIv7APM5y0V24DaWAAAAAElFTkSuQmCC',
			'6467' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7WAMYWhlCGUNDkMREpjBMZXR0aBBBEgtoYQhlbUATa2B0ZQXTCPdFRi1dunTqqpVZSO4LmSLSyuro0Ipsb0CraKhrQ8AUVDGGVtaGgAAGVLe0Mjo6OmBxM4rYQIUfFSEW9wEAuS/Lq9+BTKoAAAAASUVORK5CYII=',
			'4537' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpI37poiGMgJhCLJYiEgDa6NDgwiSGGMIiBeAIsY6RSSEAaguAMl906ZNXbpq6qqVWUjuC5gCVNXo0Ipsb2goWOcUVLeIgMQCUMVYW1kbHR1QxRhDgG5GFRuo8KMexOI+APDKzLRWLixsAAAAAElFTkSuQmCC',
			'EA2E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMYAhhCGUMDkMQCGhhDGB0dHRhQxFhbWRsC0cREGh0QYmAnhUZNW5m1MjM0C8l9YHWtjGh6RUMdpqCLAdUFYIo5OqCKhYaINLqGBqK4eaDCj4oQi/sA4MvLW1xvPGMAAAAASUVORK5CYII=',
			'D0D4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7QgMYAlhDGRoCkMQCpjCGsDY6NKKItbK2sgJJVDGRRleg6gAk90UtnbYydVVUVBSS+yDqAh0w9QaGhmDagc0tKGLY3DxQ4UdFiMV9ACFj0CyWpmHAAAAAAElFTkSuQmCC',
			'E60E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7QkMYQximMIYGIIkFNLC2MoQyOjCgiIk0Mjo6oos1sDYEwsTATgqNmha2dFVkaBaS+wIaRFuR1MHNc8Ui5ohhB6ZbsLl5oMKPihCL+wB4tMri4f+rRQAAAABJRU5ErkJggg==',
			'6809' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7WAMYQximMEx1QBITmcLayhDKEBCAJBbQItLo6OjoIIIs1sDaytoQCBMDOykyamXY0lVRUWFI7guZAlIXMBVFb6tIoyvQBHQxoBUodmBzCzY3D1T4URFicR8AOHnMUUwk6QYAAAAASUVORK5CYII=',
			'A578' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7GB1EQ1lDA6Y6IImxBogAyYCAACQxkSkgsUAHESSxgFaREIZGB5g6sJOilk5dumrpqqlZSO4LaAWqmsKAYl5oKEgnI7p5jY4O6GKsrawNqHoDWhlDgGIobh6o8KMixOI+AEPNzSC5gTBKAAAAAElFTkSuQmCC',
			'57F2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7QkNEQ11DA6Y6IIkFNDA0ujYwBARgiDE6iCCJBQYwtLICaREk94VNWzVtaeiqVVHI7mtlCACqa0S2g6GV0YEVJINsB9A0oNgUZDGRKSIgsQBkMdYAkBhjaMggCD8qQizuAwA7xMvY7CSK+QAAAABJRU5ErkJggg==',
			'D8F7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAW0lEQVR4nGNYhQEaGAYTpIn7QgMYQ1hDA0NDkMQCprC2sgJpEWSxVpFGVwwxiLoAJPdFLV0ZtjR01cosJPdB1bUyYJo3BYtYAIoY2C2MDhhuRhMbqPCjIsTiPgBxqszrchcT9gAAAABJRU5ErkJggg==',
			'03E6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7GB1YQ1hDHaY6IImxBoi0sjYwBAQgiYlMYWh0BaoWQBILaGUAqmN0QHZf1NJVYUtDV6ZmIbkPqg7FPKAY2DwRLHaIEHALNjcPVPhREWJxHwAHwMqG8dEswwAAAABJRU5ErkJggg==',
			'410F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpI37pjAEMExhDA1BFgthDGAIZXRAVscYwhrA6OiIIsYK1MvaEAgTAztp2rRVUUtXRYZmIbkvAFUdGIaGYoqB3IJuB9h9aG5hmMIaCnQzqthAhR/1IBb3AQCZXMbpIzW9swAAAABJRU5ErkJggg==',
			'325D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7RAMYQ1hDHUMdkMQCprC2sjYwOgQgq2wVaXQFiokgi01haHSdChcDO2ll1KqlSzMzs6Yhu28KEDYEouptZQjAFGN0YEUTA7qlgdHREcUtogGioQ6hjChuHqjwoyLE4j4AVH7Kp9RSSgcAAAAASUVORK5CYII=',
			'4B2F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpI37poiGMIQyhoYgi4WItDI6Ojogq2MMEWl0bQhEEWOdItLKgBADO2natKlhq1ZmhmYhuS8ApK6VEUVvaKhIo8MUVDGGKUCxAAwxoE50MdEQ1lBUtwxY+FEPYnEfAIqyyTSkiCDYAAAAAElFTkSuQmCC',
			'52FF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkMYQ1hDA0NDkMQCGlhbWRsYHRhQxEQaXdHEAgMYkMXATgqbtmrp0tCVoVnI7mtlmIJuHlAsAMOOVkYHdDERoE50MdYA0VB0twxU+FERYnEfAKD1yPtKPPoiAAAAAElFTkSuQmCC',
			'6ABB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGUMdkMREpjCGsDY6OgQgiQW0sLayNgQ6iCCLNYg0uiLUgZ0UGTVtZWroytAsJPeFTEFRB9HbKhrqim5eK1AdmpgIFr2sAUAxNDcPVPhREWJxHwAp9M1LbBu6OgAAAABJRU5ErkJggg==',
			'09B7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGUNDkMRYA1hbWRsdGkSQxESmiDS6NgSgiAW0AsWA6gKQ3Be1dOnS1NBVK7OQ3BfQyhgIVNfKgKKXAWTeFAYUO1hAYgEMGG5xdMDiZhSxgQo/KkIs7gMAsrvMXjtnD/QAAAAASUVORK5CYII=',
			'AF1E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7GB1EQx2mMIYGIImxBog0MIQwOiCrE5ki0sCIJhbQClQ3BS4GdlLU0qlhq6atDM1Cch+aOjAMDcUUw6YOlxhjqCOKmwcq/KgIsbgPAAqEygJvifClAAAAAElFTkSuQmCC',
			'B333' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QgNYQxhDGUIdkMQCpoi0sjY6OgQgi7UyNDo0BDSIoKhjgIoi3BcatSps1dRVS7OQ3IemDrd5WO3AdAs2Nw9U+FERYnEfAAqVz05Httx3AAAAAElFTkSuQmCC',
			'52A9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAe0lEQVR4nGNYhQEaGAYTpIn7QkMYQximMEx1QBILaGBtZQhlCAhAERNpdHR0dBBBEgsMYGh0bQiEiYGdFDZt1dKlq6KiwpDd18owhbUhYCqyXqBYAGso0FRkO1oZHYDqUOwQAeoEiqG4hTVANNQVaB6ymwcq/KgIsbgPANu1zNHcsuQuAAAAAElFTkSuQmCC',
			'5B3D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkNEQxhDGUMdkMQCGkRaWRsdHQJQxRodGgIdRJDEAgNEWhmA6kSQ3Bc2bWrYqqkrs6Yhu68VRR1MDMO8ACxiIlMw3cIagOnmgQo/KkIs7gMA5EjMppMlnmkAAAAASUVORK5CYII=',
			'3947' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7RAMYQxgaHUNDkMQCprC2MrQ6NIggq2wVaXSYiiY2BSgW6NAQgOS+lVFLl2ZmZq3MQnbfFMZA10aHVhSbWxkaXUMDpqCKsTQ6NDoEMKC7pdHRAYubUcQGKvyoCLG4DwBXgszccmqIdgAAAABJRU5ErkJggg==',
			'C138' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WEMYAhhDGaY6IImJtDIGsDY6BAQgiQU0sgYwNAQ6iCCLNTAEMCDUgZ0UBUJTV03NQnIfmjqEGLp5jZhiIq0MGG5hDWENRXfzQIUfFSEW9wEALd/LZHstWTIAAAAASUVORK5CYII=',
			'BFDB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QgNEQ11DGUMdkMQCpog0sDY6OgQgi7UCxRoCHUTQ1QHFApDcFxo1NWzpqsjQLCT3oanDbR4uO9DcEhoAFENz80CFHxUhFvcBADK8zceCgV0uAAAAAElFTkSuQmCC',
			'79AD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7QkMZQximMIY6IIu2srYyhDI6BKCIiTQ6Ojo6iCCLTRFpdG0IhIlB3BS1dGnqqsisaUjuY3RgDERSB4asDQyNrqGoYiINLI3o6gIaWFtZgWIBKGKMIUAxVDcPUPhREWJxHwCF+Mv4IvVbMQAAAABJRU5ErkJggg==',
			'EAEF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAW0lEQVR4nGNYhQEaGAYTpIn7QkMYAlhDHUNDkMQCGhhDWBsYHRhQxFhbMcVEGl0RYmAnhUZNW5kaujI0C8l9aOqgYqKhmGLY1GGKhYYAxUIdUcQGKvyoCLG4DwBiIsroECbW4wAAAABJRU5ErkJggg==',
			'C145' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WEMYAhgaHUMDkMREWhkDGFodHZDVBTSyBjBMRRNrAOoNdHR1QHJfFBCtzMyMikJyH0gda6NDgwiaXlagrShijWC3OIiguAUk5hCA7D7WENZQoNhUh0EQflSEWNwHAPSQype462SDAAAAAElFTkSuQmCC',
			'9AF5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYAlhDA0MDkMREpjCGsDYwOiCrC2hlbcUUE2l0bWB0dUBy37Sp01amhq6MikJyH6srSB3QXGSbW0VD0cUEIOY5IIuJTAHrDUB2H2sAWGyqwyAIPypCLO4DAKHay0j8epwPAAAAAElFTkSuQmCC',
			'7D5E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkNFQ1hDHUMDkEVbRVpZGxgdGFDFGl3RxaYAxabCxSBuipq2MjUzMzQLyX2MDiKNDg2BKHpZGzDFRBpAdqCKBTSItDI6OqKJiYYwhDKiunmAwo+KEIv7AAcgypCB21NyAAAAAElFTkSuQmCC',
			'DBA9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7QgNEQximMEx1QBILmCLSyhDKEBCALNYq0ujo6OgggirWytoQCBMDOylq6dSwpauiosKQ3AdRFzAVTW+ja2hAA4ZYQwCqHVPAelHcAnIzyDxkNw9U+FERYnEfADCxzuEYR7qJAAAAAElFTkSuQmCC',
			'4DD4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpI37poiGsIYyNAQgi4WItLI2OjQiizGGiDS6NgS0IouxTgGLTQlAct+0adNWpq6KiopCcl8AWF2gA7Le0FCwWGgIilvA5qG6ZQrYLWhiWNw8UOFHPYjFfQA2tc+OZ/Mc6gAAAABJRU5ErkJggg==',
			'5773' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7QkNEQ11DA0IdkMQCGhgaHRoCHQIwxAIaRJDEAgMYWiGiCPeFTVs1bdXSVUuzkN3XyhDAMIWhAdk8hlZGB6AoinkBrawNQFEUMZEpIg0gUWS9rAEgMQYUNw9U+FERYnEfAPIzzRb9EZOzAAAAAElFTkSuQmCC',
			'289C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGaYGIImJTGFtZXR0CBBBEgtoFWl0bQh0YEHW3craygoUQ3HftJVhKzMjs1DcF8DayhACVweGjA4ijQ4NqGKsDSKNjmh2iDRguiU0FNPNAxV+VIRY3AcADqLKgZdcloEAAAAASUVORK5CYII=',
			'69A1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYQximMLQii4lMYW1lCGWYiiwW0CLS6OjoEIoi1iDS6NoQANMLdlJk1NKlqauiliK7L2QKYyCSOojeVoZG11B0MZZGdHUgt7CiiYHcDBQLDRgE4UdFiMV9AJurzZtVK7FgAAAAAElFTkSuQmCC',
			'019F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7GB0YAhhCGUNDkMRYAxgDGB0dHZDViUxhDWBtCEQRC2hlQBYDOylq6aqolZmRoVlI7gOpYwjB1MuAZp7IFIYARjQx1gAGDLcwOrCGAt2MIjZQ4UdFiMV9AF6+xqICv/OSAAAAAElFTkSuQmCC',
			'BAD6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QgMYAlhDGaY6IIkFTGEMYW10CAhAFmtlbWVtCHQQQFEn0ugKFEN2X2jUtJWpqyJTs5DcB1WHZp5oKEivCIoYxDwRdDvQ3BIaABRDc/NAhR8VIRb3AQBDds77i8OPIgAAAABJRU5ErkJggg==',
			'E21D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkMYQximMIY6IIkFNLC2MoQwOgSgiIk0OgLFRFDEGBodpsDFwE4KjVq1dNW0lVnTkNwHVDeFYQqG3gBMMUYHTDHWBpAYsltCQ0RDHYEQ2c0DFX5UhFjcBwB+fsurb5zHzAAAAABJRU5ErkJggg==',
			'28BA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDGVqRxUSmsLayNjpMdUASC2gVaXRtCAgIQNbdClLn6CCC7L5pK8OWhq7MmobsvgAUdWDI6AAyLzA0BNktDWAxFHUiDZh6Q0NBbmZEERuo8KMixOI+ANECy8GQyeIxAAAAAElFTkSuQmCC',
			'E1B7' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkMYAlhDGUNDkMQCGhgDWBsdGkRQxFgDWIEkqhgDWF0AkvtCo1ZFLQ1dtTILyX1Qda0M6HobAqZgEQvAEGt0dEB1M2so0M0oYgMVflSEWNwHAIShy2/v37/IAAAAAElFTkSuQmCC',
			'5FA4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkNEQx2mMDQEIIkFNIg0MIQyNKKLMTo6tCKLBQaINLA2BEwJQHJf2LSpYUtXRUVFIbuvFaQu0AFZL1gsNDA0BNkOsLoAFLeITMEUYw3AFBuo8KMixOI+AF2wztL8uxIGAAAAAElFTkSuQmCC',
			'1447' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nM3QsRGAIAyF4UfBBrDPs6DHOxlCpoCCDWQECpzSNh6Uepp0f5PvgnOYhD/tKz5FFOQlbKJpoqIwGdEsEVDvTVE5rExe+Hpsre+xR+FTNEVnlvtdG1zwx2ihn/gom93G9tX/HtyJ7wLwYMlzNo1XOgAAAABJRU5ErkJggg==',
			'A129' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7GB0YAhhCGaY6IImxBjAGMDo6BAQgiYlMYQ1gbQh0EEESC2gF6kWIgZ0UtXRV1KqVWVFhSO4Dq2tlmIqsNzQUKDYFaC66eQEMGHaA3BiAIsYayhoagOLmgQo/KkIs7gMApy3JoTcrH8QAAAAASUVORK5CYII=',
			'0090' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7GB0YAhhCGVqRxVgDGEMYHR2mOiCJiUxhbWVtCAgIQBILaBVpdG0IdBBBcl/U0mkrMzMjs6YhuQ+kziEErg4h1oAqBrKDEc0ObG7B5uaBCj8qQizuAwDKIsscXZFNXgAAAABJRU5ErkJggg==',
			'097F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDA0NDkMRYA1hbGRoCHZDViUwRaXRAEwtoBYo1OsLEwE6KWrp0adbSlaFZSO4LaGUMdJjCiKaXodEhgBHNDhagaahiILewNqCKgd2MJjZQ4UdFiMV9ADoLyXJMQMAaAAAAAElFTkSuQmCC',
			'9D43' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANEQxgaHUIdkMREpoi0MrQ6OgQgiQW0ijQ6THVoEEEXC3RoCEBy37Sp01ZmZmYtzUJyH6urSKNrI1wdBAL1uoYGoJgnADKvEdUOsFsaUd2Czc0DFX5UhFjcBwAgDc58de4yqgAAAABJRU5ErkJggg=='        
        );
        $this->text = array_rand( $images );
        return $images[ $this->text ] ;    
    }
    
    function out_processing_gif(){
        $image = dirname(__FILE__) . '/processing.gif';
        $base64_image = "R0lGODlhFAAUALMIAPh2AP+TMsZiALlcAKNOAOp4ANVqAP+PFv///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAIACwAAAAAFAAUAAAEUxDJSau9iBDMtebTMEjehgTBJYqkiaLWOlZvGs8WDO6UIPCHw8TnAwWDEuKPcxQml0Ynj2cwYACAS7VqwWItWyuiUJB4s2AxmWxGg9bl6YQtl0cAACH5BAUKAAgALAEAAQASABIAAAROEMkpx6A4W5upENUmEQT2feFIltMJYivbvhnZ3Z1h4FMQIDodz+cL7nDEn5CH8DGZhcLtcMBEoxkqlXKVIgAAibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkphaA4W5upMdUmDQP2feFIltMJYivbvhnZ3V1R4BNBIDodz+cL7nDEn5CH8DGZAMAtEMBEoxkqlXKVIg4HibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpjaE4W5tpKdUmCQL2feFIltMJYivbvhnZ3R0A4NMwIDodz+cL7nDEn5CH8DGZh8ONQMBEoxkqlXKVIgIBibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpS6E4W5spANUmGQb2feFIltMJYivbvhnZ3d1x4JMgIDodz+cL7nDEn5CH8DGZgcBtMMBEoxkqlXKVIggEibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpAaA4W5vpOdUmFQX2feFIltMJYivbvhnZ3V0Q4JNhIDodz+cL7nDEn5CH8DGZBMJNIMBEoxkqlXKVIgYDibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpz6E4W5tpCNUmAQD2feFIltMJYivbvhnZ3R1B4FNRIDodz+cL7nDEn5CH8DGZg8HNYMBEoxkqlXKVIgQCibbK9YLBYvLtHH5K0J0IACH5BAkKAAgALAEAAQASABIAAAROEMkpQ6A4W5spIdUmHQf2feFIltMJYivbvhnZ3d0w4BMAIDodz+cL7nDEn5CH8DGZAsGtUMBEoxkqlXKVIgwGibbK9YLBYvLtHH5K0J0IADs=";
        $binary = is_file($image) ? join("",file($image)) : base64_decode($base64_image); 
        header("Cache-Control: post-check=0, pre-check=0, max-age=0, no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: image/gif");
        echo $binary;
    }

}
# end of class phpfmgImage
# ------------------------------------------------------
# end of module : captcha


# module user
# ------------------------------------------------------
function phpfmg_user_isLogin(){
    return ( isset($_SESSION['authenticated']) && true === $_SESSION['authenticated'] );
}


function phpfmg_user_logout(){
    session_destroy();
    header("Location: admin.php");
}

function phpfmg_user_login()
{
    if( phpfmg_user_isLogin() ){
        return true ;
    };
    
    $sErr = "" ;
    if( 'Y' == $_POST['formmail_submit'] ){
        if(
            defined( 'PHPFMG_USER' ) && strtolower(PHPFMG_USER) == strtolower($_POST['Username']) &&
            defined( 'PHPFMG_PW' )   && strtolower(PHPFMG_PW) == strtolower($_POST['Password']) 
        ){
             $_SESSION['authenticated'] = true ;
             return true ;
             
        }else{
            $sErr = 'Login failed. Please try again.';
        }
    };
    
    // show login form 
    phpfmg_admin_header();
?>
<form name="frmFormMail" action="" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:380px;height:260px;">
<fieldset style="padding:18px;" >
<table cellspacing='3' cellpadding='3' border='0' >
	<tr>
		<td class="form_field" valign='top' align='right'>Email :</td>
		<td class="form_text">
            <input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" class='text_box' >
		</td>
	</tr>

	<tr>
		<td class="form_field" valign='top' align='right'>Password :</td>
		<td class="form_text">
            <input type="password" name="Password"  value="" class='text_box'>
		</td>
	</tr>

	<tr><td colspan=3 align='center'>
        <input type='submit' value='Login'><br><br>
        <?php if( $sErr ) echo "<span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
        <a href="admin.php?mod=mail&func=request_password">I forgot my password</a>   
    </td></tr>
</table>
</fieldset>
</div>
<script type="text/javascript">
    document.frmFormMail.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();
}


function phpfmg_mail_request_password(){
    $sErr = '';
    if( $_POST['formmail_submit'] == 'Y' ){
        if( strtoupper(trim($_POST['Username'])) == strtoupper(trim(PHPFMG_USER)) ){
            phpfmg_mail_password();
            exit;
        }else{
            $sErr = "Failed to verify your email.";
        };
    };
    
    $n1 = strpos(PHPFMG_USER,'@');
    $n2 = strrpos(PHPFMG_USER,'.');
    $email = substr(PHPFMG_USER,0,1) . str_repeat('*',$n1-1) . 
            '@' . substr(PHPFMG_USER,$n1+1,1) . str_repeat('*',$n2-$n1-2) . 
            '.' . substr(PHPFMG_USER,$n2+1,1) . str_repeat('*',strlen(PHPFMG_USER)-$n2-2) ;


    phpfmg_admin_header("Request Password of Email Form Admin Panel");
?>
<form name="frmRequestPassword" action="admin.php?mod=mail&func=request_password" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:580px;height:260px;text-align:left;">
<fieldset style="padding:18px;" >
<legend>Request Password</legend>
Enter Email Address <b><?php echo strtoupper($email) ;?></b>:<br />
<input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" style="width:380px;">
<input type='submit' value='Verify'><br>
The password will be sent to this email address. 
<?php if( $sErr ) echo "<br /><br /><span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
</fieldset>
</div>
<script type="text/javascript">
    document.frmRequestPassword.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();    
}


function phpfmg_mail_password(){
    phpfmg_admin_header();
    if( defined( 'PHPFMG_USER' ) && defined( 'PHPFMG_PW' ) ){
        $body = "Here is the password for your form admin panel:\n\nUsername: " . PHPFMG_USER . "\nPassword: " . PHPFMG_PW . "\n\n" ;
        if( 'html' == PHPFMG_MAIL_TYPE )
            $body = nl2br($body);
        mailAttachments( PHPFMG_USER, "Password for Your Form Admin Panel", $body, PHPFMG_USER, 'You', "You <" . PHPFMG_USER . ">" );
        echo "<center>Your password has been sent.<br><br><a href='admin.php'>Click here to login again</a></center>";
    };   
    phpfmg_admin_footer();
}


function phpfmg_writable_check(){
 
    if( is_writable( dirname(PHPFMG_SAVE_FILE) ) && is_writable( dirname(PHPFMG_EMAILS_LOGFILE) )  ){
        return ;
    };
?>
<style type="text/css">
    .fmg_warning{
        background-color: #F4F6E5;
        border: 1px dashed #ff0000;
        padding: 16px;
        color : black;
        margin: 10px;
        line-height: 180%;
        width:80%;
    }
    
    .fmg_warning_title{
        font-weight: bold;
    }

</style>
<br><br>
<div class="fmg_warning">
    <div class="fmg_warning_title">Your form data or email traffic log is NOT saving.</div>
    The form data (<?php echo PHPFMG_SAVE_FILE ?>) and email traffic log (<?php echo PHPFMG_EMAILS_LOGFILE?>) will be created automatically when the form is submitted. 
    However, the script doesn't have writable permission to create those files. In order to save your valuable information, please set the directory to writable.
     If you don't know how to do it, please ask for help from your web Administrator or Technical Support of your hosting company.   
</div>
<br><br>
<?php
}


function phpfmg_log_view(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    
    phpfmg_admin_header();
   
    $file = $files[$n];
    if( is_file($file) ){
        if( 1== $n ){
            echo "<pre>\n";
            echo join("",file($file) );
            echo "</pre>\n";
        }else{
            $man = new phpfmgDataManager();
            $man->displayRecords();
        };
     

    }else{
        echo "<b>No form data found.</b>";
    };
    phpfmg_admin_footer();
}


function phpfmg_log_download(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );

    $file = $files[$n];
    if( is_file($file) ){
        phpfmg_util_download( $file, PHPFMG_SAVE_FILE == $file ? 'form-data.csv' : 'email-traffics.txt', true, 1 ); // skip the first line
    }else{
        phpfmg_admin_header();
        echo "<b>No email traffic log found.</b>";
        phpfmg_admin_footer();
    };

}


function phpfmg_log_delete(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    phpfmg_admin_header();

    $file = $files[$n];
    if( is_file($file) ){
        echo unlink($file) ? "It has been deleted!" : "Failed to delete!" ;
    };
    phpfmg_admin_footer();
}


function phpfmg_util_download($file, $filename='', $toCSV = false, $skipN = 0 ){
    if (!is_file($file)) return false ;

    set_time_limit(0);


    $buffer = "";
    $i = 0 ;
    $fp = @fopen($file, 'rb');
    while( !feof($fp)) { 
        $i ++ ;
        $line = fgets($fp);
        if($i > $skipN){ // skip lines
            if( $toCSV ){ 
              $line = str_replace( chr(0x09), ',', $line );
              $buffer .= phpfmg_data2record( $line, false );
            }else{
                $buffer .= $line;
            };
        }; 
    }; 
    fclose ($fp);
  

    
    /*
        If the Content-Length is NOT THE SAME SIZE as the real conent output, Windows+IIS might be hung!!
    */
    $len = strlen($buffer);
    $filename = basename( '' == $filename ? $file : $filename );
    $file_extension = strtolower(substr(strrchr($filename,"."),1));

    switch( $file_extension ) {
        case "pdf": $ctype="application/pdf"; break;
        case "exe": $ctype="application/octet-stream"; break;
        case "zip": $ctype="application/zip"; break;
        case "doc": $ctype="application/msword"; break;
        case "xls": $ctype="application/vnd.ms-excel"; break;
        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpg"; break;
        case "mp3": $ctype="audio/mpeg"; break;
        case "wav": $ctype="audio/x-wav"; break;
        case "mpeg":
        case "mpg":
        case "mpe": $ctype="video/mpeg"; break;
        case "mov": $ctype="video/quicktime"; break;
        case "avi": $ctype="video/x-msvideo"; break;
        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
        case "php":
        case "htm":
        case "html": 
                $ctype="text/plain"; break;
        default: 
            $ctype="application/x-download";
    }
                                            

    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    header("Content-Disposition: attachment; filename=".$filename.";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
    
    while (@ob_end_clean()); // no output buffering !
    flush();
    echo $buffer ;
    
    return true;
 
    
}
?>