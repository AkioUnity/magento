<?php
$selectorMaxWidth = (int)self::$options->getValue('selector-max-width');
if(!empty($magicscroll) && !empty($magicscrollOptions)) {
    $magicscrollOptions = " data-options=\"{$magicscrollOptions}\"";
} else {
    $magicscrollOptions = '';
}
?>
<!-- Begin magiczoom -->
<div class="MagicToolboxContainer selectorsLeft minWidth<?php echo empty($magicscroll)?' noscroll':'' ?>">
<?php

if(is_array($thumbs)) {
    $thumbs = array_unique($thumbs);
}

if(count($thumbs) > 1) {
    ?>
    <div class="MagicToolboxSelectorsContainer" style="flex-basis: <?php echo $selectorMaxWidth ?>px; width: <?php echo $selectorMaxWidth ?>px;">
        <div id="MagicToolboxSelectors<?php echo $pid ?>" class="<?php echo $magicscroll ?>"<?php echo $magicscrollOptions ?>>
        <?php echo join("\n\t", $thumbs); ?>
        </div>
    </div>
    <?php
        if(!empty($magicscroll) && !is_numeric(self::$options->getValue('height'))) {
            ?>
            <script type="text/javascript">
                mzOptions = mzOptions || {};
                mzOptions.onUpdate = function() {
                    MagicScroll.resize('MagicToolboxSelectors<?php echo $pid ?>');
                };
            </script>
            <?php
        }
        ?>
    <?php
}
?>
    <div class="MagicToolboxMainContainer">
        <?php echo $main; ?>
    </div>
</div>
<script type="text/javascript">
    if (window.matchMedia("(max-width: 767px)").matches) {
        $scroll = document.getElementById('MagicToolboxSelectors<?php echo $pid ?>');
        if ($scroll && typeof $scroll != 'undefined') {
            $attr = $scroll.getAttribute('data-options');
            if ($attr !== null) {
                $scroll.setAttribute('data-options',$attr/*.replace(/autostart *\: *false/gm,'')*/.replace(/orientation *\: *[a-zA-Z]{1,}/gm,'orientation:horizontal'));
                if (typeof mzOptions != 'undefined') {
                    mzOptions.onUpdate = function() {};
                }
            }
        }
    } else {
      
    }    
</script>
<!-- End magiczoom -->
