<?php

// if the from is loaded from WordPress form loader plugin, 
// the phpfmg_display_form() will be called by the loader 
if( !defined('FormmailMakerFormLoader') ){
    # This block must be placed at the very top of page.
    # --------------------------------------------------
	require_once( dirname(__FILE__).'/form.lib.php' );
    phpfmg_display_form();
    # --------------------------------------------------
};


function phpfmg_form( $sErr = false ){
		$style=" class='form_text' ";

?>
<div class="top-container">
  <div class="div-top-logo"><img src="libs/img/oak-appartments-logo.png" alt="OAK Appartments logo"  id="oak-logo"></div>
  <div class="div-top-middle">1 BEDROOM STARTING FROM<br>
    <strong>$295,000.00</strong><br>
    2 BEDROOM STARTING FROM<br>
    <strong>$375,000.00</strong>
  </div>
  <div class="div-top-form"><span class="form-title">REGISTER YOUR INTEREST NOW</span>
    <div id='frmFormMailContainer'>
        <form name="frmFormMail" id="frmFormMail" target="submitToFrame" action='<?php echo PHPFMG_ADMIN_URL . '' ; ?>' method='post' enctype='multipart/form-data' onsubmit='return fmgHandler.onSubmit(this);'>
        <input type='hidden' name='formmail_submit' value='Y'>
        <input type='hidden' name='mod' value='ajax'>
        <input type='hidden' name='func' value='submit'>
        <div id="form-content">
            <span><input type="text" name="field_0"  id="field_0_div" value="<?php  phpfmg_hsc("field_0", ""); ?>" class='text_box' placeholder="FIRST NAME*"></span>
            <span><input type="text" name="field_1"  id="field_1_div" value="<?php  phpfmg_hsc("field_1", ""); ?>" class='text_box' placeholder="LAST NAME*"></span>
            <span><input type="text" name="field_3"  id="field_3_div" value="<?php  phpfmg_hsc("field_3", ""); ?>" class='text_box' placeholder="PHONE*"></span>
            <span><input type="text" name="field_2"  id="field_2_div" value="<?php  phpfmg_hsc("field_2", ""); ?>" class='text_box' placeholder="POSTCODE*"></span>
            <span><input type="text" name="field_4"  id="field_4_div" value="<?php  phpfmg_hsc("field_4", ""); ?>" class='text_box' placeholder="EMAIL*"></span>
            <span><input type="text" name="field_5"  id="field_5_div" value="<?php  phpfmg_hsc("field_5", ""); ?>" class='text_box' placeholder="COMMENTS"></span>
                <div class='form_submit_block col_field'> 
                <div id="align-left"><span><hr id="hr-left"></span><input type='submit' value='SUBMIT' class='form_button'><span><hr id="hr-right"></span></div>
        </div>  
                <div id='err_required' class="form_error" style='display:none;'>
                    <label class='form_error_title'>Please check the required fields</label>
                </div>        
                <span id='phpfmg_processing' style='display:none;'>
                    <img id='phpfmg_processing_gif' src='<?php echo PHPFMG_ADMIN_URL . '?mod=image&amp;func=processing' ;?>' border=0 alt='Processing...'> <label id='phpfmg_processing_dots'></label>
                </span>
            </div>
        </form>
    </div>
<iframe name="submitToFrame" id="submitToFrame" src="javascript:false" style="position:absolute;top:-10000px;left:-10000px;" /></iframe>

</div> 
<!-- end of form container -->


<!-- [Your confirmation message goes here] -->
<div id='thank_you_msg' style='display:none;'>
Your form has been sent. Thank you!
</div>
</div>

</div>
  </div>
        <div class="flexslider">
          <ul class="slides">
            <li>
                <img src="images/kitchen_adventurer_cheesecake_brownie.jpg" />
                </li>
                <li>
                <img src="images/kitchen_adventurer_lemon.jpg" />
                </li>
                <li>
                <img src="images/kitchen_adventurer_donut.jpg" />
                </li>
                <li>
                <img src="images/kitchen_adventurer_caramel.jpg" />
                </li>
          </ul>
        </div>

  </div>
  <div class="div-contact"><span id="name">Felicity Paglia<br>
0401 040 090<br></span>
<img src="libs/img/email.png"><br>
<hr>
12 MONTHS RENTAL GUARANTEE<br>
FOR ALL INVESTORS</div>
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.min.js">\x3C/script>')</script>
  <!-- FlexSlider -->
  <script defer src="libs/js/jquery.flexslider.js"></script>
  <script type="text/javascript">
    $(function(){
      SyntaxHighlighter.all();
    });
    $(window).load(function(){
      $('.flexslider').flexslider({
        animation: "slide",
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });
  </script>
<?php
			
    phpfmg_javascript($sErr);

} 
# end of form




function phpfmg_form_css(){
    $formOnly = isset($GLOBALS['formOnly']) && true === $GLOBALS['formOnly'];
?>
    <link rel="stylesheet" href="libs/css/flexslider.css" type="text/css" media="screen" />
  <link rel="stylesheet" href="libs/css/style.css" type="text/css" media="screen" />
  <script src="libs/js/modernizr.js"></script>
<style type='text/css'>
<?php 
if( !$formOnly ){
    echo"

select, option{
    font-size:13px;
}
";
}; // if
?>
.form_error_title{
    font-weight: bold;
    color: red;
}
div.instruction_error{
    color: red;
    font-weight:bold;
}

hr.sectionbreak{
    height:1px;
    color: #ccc;
}

<?php phpfmg_text_align();?>    



</style>

<?php
}
# 
 
#  
?>