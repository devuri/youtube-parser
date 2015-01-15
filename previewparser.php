<?php
/*
  Plugin Name: YouTube Parser
  Plugin URI: http://sadesign.pro/services/youtubeparser/
  Description: Take away video screenshots from a link. Locate shortcode [previewparser] on the page.
  Version: 1.0
  Author: Sadesign Studio
  Author URI: http://sadesign.pro
 */
function ytp_init() {
  load_plugin_textdomain( 'previewparser', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'ytp_init' );

add_action('wp_enqueue_scripts', 'ytp_styles');

function ytp_styles() {
  wp_enqueue_style('preparserstyle', plugins_url('css/style.css', __FILE__));
}

require_once 'class.previewparser.php';

add_shortcode('previewparser', 'ytp_parser');
function ytp_parser($args) {
  extract( shortcode_atts( array(
        'h-width' => false,
        'uridef' => $args['uridef'],
    ), $args ) );
  if(empty($args['uridef'])) {
    $args['uridef'] = 'c6bEs3dxjPg';
  }
  $class = '';
  $w = $args['h-width'];
  switch($w) {
    case 'full' :
      $width = '100%';
      break;
    case '0' :
      $width = '980px';
      break;
    default :
      $width = $w . 'px';
  }
  if(isset($_GET['url']) && !empty($_GET['url'])) {
    $uriRaw = htmlspecialchars($_GET["url"]);
  } else
  if (isset($_POST["you"]["url"])) {
    $uriRaw = htmlspecialchars($_POST["you"]["url"]);
  }
  $previewVideoStatic = new ytp_previewParserObject($uriRaw, $args['uridef']);
  if($width) {
  ?>
<style>
  .pp-h-width {
    width: <?php echo $width; ?>;
  }
</style>
  <?php } ?>
<div class="preparser">

  <div class="preparser-header pp-row pp-h-width">
      <div class="pp-width">
        <h1 class="preparser-title">YouTube Preview</h1>
        <div class="preparser-desc"><?php echo __('Get an available preview of the image for your video on Youtube', 'previewparser'); ?></div>
      </div>
      <form method="POST" class="preparser-form pp-col w8-12 off2" id="preparser-form" name="preparser-form">
        <input class="preparser-form-input" type="text" data-def="<?php echo $previewVideoStatic->uriDef; ?>" name="you[url]" value="<?php echo $uriRaw ?>" size="40">
        <input class="preparser-form-button" type="submit" id="preparser-form-submit" value="Получить">
      </form>
      <div class="pp-row">
        <div class="preparser-desc-bottom">
          <?php echo __('Copy the address of the video from YouTube and we\'ll show you all available preview of the image', 'previewparser'); ?>
        </div>
      </div>
    </div>

  <div class="pp-row preparser-result-wr pp-width">
    <div class="pp-col w8-12 off2 preparser-result">
      <div class="js-result-small-wr">
          <div class="preparser-img-desc"><?php echo __('Thumbnail size 120&times;90', 'previewparser'); ?></div>
          <div class="preparser-result-small preparser-result-container js-result-small">
            <?php echo $previewVideoStatic->getSmallImg(); ?>
          </div>
        </div>
        <div class="js-result-medium-wr pp-row">
          <div class="preparser-img-desc"><?php echo __('Normal size 480&times;360', 'previewparser'); ?></div>
          <div class="preparser-result-medium preparser-result-container js-result-medium">
            <?php echo $previewVideoStatic->getMediumImg(); ?>
          </div>
        </div>
        <div class="js-result-full-wr pp-row">
          <div class="preparser-img-desc"><?php echo __('Full size 1920&times;1080', 'previewparser'); ?></div>
          <div class="preparser-result-full preparser-result-container js-result-full">
            <?php echo $previewVideoStatic->getFullImg(); ?>
          </div>
        </div>
        <div class="preparser-result-iframe preparser-result-container js-result-iframe">
          <iframe width="640" height="360" src="//www.youtube.com/embed/<?php echo $previewVideoStatic->id; ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
  </div>
</div><?php
}

add_action('wp_ajax_previewparser', 'ytp_parserCallback');
add_action('wp_ajax_nopriv_previewparser', 'ytp_parserCallback');

function ytp_parserCallback() {
  $previewVideoAjax = new ytp_previewParserObject($_POST['uriRaw'], $_POST['uriDef']);
  $templatesSmall = $previewVideoAjax->getSmallImg();
  $templatesMedium = $previewVideoAjax->getMediumImg();
  $templatesFull = $previewVideoAjax->getFullImg();
  $src = '//www.youtube.com/embed/' . $previewVideoAjax->id;
  
  wp_send_json(
          array(
              'small' => $templatesSmall, 
              'medium' => $templatesMedium, 
              'full' => $templatesFull, 
              'iframe' => $src)
          );
  die();
}

add_action('wp_footer', 'ytp_javascript');

function ytp_javascript() {
  ?>
  <script type="text/javascript" id="ytp_preparser-js">
      jQuery(function ($) {
        $('.preparser-form-input').click(function(){
          $(this).select();
        });
        $("#preparser-form").submit(function () {
          var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
          var uriRaw = $(".preparser-form-input").val();
          var uriDef = $(".preparser-form-input").attr('data-def');
          if(!(uriRaw)) {
            uriRaw = uriDef;
          }
          $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
              action: 'previewparser',
              uriRaw: uriRaw,
              uriDef: uriDef
            },
            beforeSend: function () {
              $('.preparser-result-wr').css({'height': $('.preparser-result-wr').height()});
              $('.preparser-result-wr').addClass('waiting');
            },
            success: function (data)
            {
              console.log(data);
              if(data.small) {
                $('.js-result-small-wr').show();
                $('.js-result-small').html(data.small);
              } else {
                $('.js-result-small-wr').hide();
              }
              if(data.medium) {
                $('.js-result-medium-wr').show();
                $('.js-result-medium').html(data.medium);
              } else {
                $('.js-result-medium-wr').hide();
              }
              if(data.full) {
                $('.js-result-full-wr').show();
                $('.js-result-full').html(data.full);
              } else {
                $('.js-result-full-wr').hide();
              }
              $('.js-result-iframe iframe').attr('src', data.iframe);
              $('.preparser-result-wr').removeClass('waiting');
              $('.preparser-result-wr').css({'height': ''});
              if(uriRaw == uriDef) {
                window.history.pushState('', '', location.protocol + '//' + location.host + location.pathname);
              } else {
                window.history.pushState('', '', location.protocol + '//' + location.host + location.pathname + '?url=' + uriRaw);
              }

            }
          });
          return false; // avoid to execute the actual submit of the form.
        });
        $(window).load(function(){
//          $(".fancybox").attr('rel', 'gallery').fancybox({live: false});
//          $('.fancybox.preparser-img-wr').off('click.fb-start');
        });

      });
  </script>
  <?php
}
