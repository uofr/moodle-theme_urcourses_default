<?php
require_once("../../../../config.php");
$accordionid = uniqid('accordion_');
?>
<div class="mceTmpl">
    <div class="accordion ur-accordion-block" id="<?php echo $accordionid; ?>">

      <!-- Accordion Item 1 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center" id="headingOne_<?php echo $accordionid; ?>">
        <h2 class="mb-0 flex-grow-1">
             <button class="btn btn-link"
                    type="button"
                    data-toggle="collapse"
                    data-target="#collapseOne_<?php echo $accordionid; ?>"
                    aria-expanded="true"
                    aria-controls="collapseOne_<?php echo $accordionid; ?>">
            <span class="accordion-title"
                    data-title-id="title1_<?php echo $accordionid; ?>">Accordion Item #1</span>
            </button>
        </h2>
      </div>
      <div id="collapseOne_<?php echo $accordionid; ?>" class="collapse show"
           aria-labelledby="headingOne_<?php echo $accordionid; ?>"
           data-parent="#<?php echo $accordionid; ?>">
        <div class="card-body mceEditable" contenteditable="true">
        Paragraph lorem ipsum dolor sit amet, bold text consectetur adipiscing elit. Aliquam luctus tristique ligula, italic text eu pretium nunc bold italic text bibendum et. Suspendisse diam dui, gravida fermentum auctor eget, luctus a eros. Link Nunc lacinia nunc ut nulla lacinia, vel porttitor nisl sollicitudin. bold link Cras ut orci porta, placerat nibh sed, iaculis purus. italic link Mauris eros augue, tristique at ante eget, hendrerit mattis erat. bold italic link Cras elit nisi, scelerisque nec tincidunt pellentesque, pretium id urna. 
        </div>
      </div>
    </div>

    </div>
</div>
