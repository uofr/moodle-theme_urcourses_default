<?php
require_once("../../../../config.php");
$accordionid = uniqid('accordion_');
?>
<div class="mceTmpl">
    <div class="accordion ur-accordion-block" id="<?php echo $accordionid; ?>">

        <div class="card">
            <div class="card-header" id="headingOne_<?php echo $accordionid; ?>">
                <h2 class="mb-0">
                    <a class="btn btn-link"  data-toggle="collapse" href="#collapseOne_<?php echo $accordionid; ?>" role="button" aria-expanded="true" aria-controls="collapseOne_<?php echo $accordionid; ?>">
                      <span class="mceEditable" contenteditable="true">Accordion Item #1</span>
                    </a>
                </h2>
            </div>

            <div id="collapseOne_<?php echo $accordionid; ?>" class="collapse show" aria-labelledby="headingOne_<?php echo $accordionid; ?>" data-parent="#<?php echo $accordionid; ?>">
                <div class="card-body mceEditable">
                   Paragraph lorem ipsum dolor sit amet, bold text consectetur adipiscing elit. Aliquam luctus tristique ligula, italic text eu pretium nunc bold italic text bibendum et. Suspendisse diam dui, gravida fermentum auctor eget, luctus a eros. Link Nunc lacinia nunc ut nulla lacinia, vel porttitor nisl sollicitudin. bold link Cras ut orci porta, placerat nibh sed, iaculis purus. italic link Mauris eros augue, tristique at ante eget, hendrerit mattis erat. bold italic link Cras elit nisi, scelerisque nec tincidunt pellentesque, pretium id urna. 
                </div>
            </div>
        </div>

    </div>
</div>
