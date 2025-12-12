<?php
require_once("../../../../config.php");
$accordionid = uniqid('accordion_');
?>

<div class="mceTmpl">
  <div id="<?php echo $accordionid; ?>" class="accordion ur-accordion-block">

    <!-- Accordion Item 1 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center" id="headingOne_<?php echo $accordionid; ?>">
        <h2 class="mb-0 flex-grow-1">
      <button class="btn btn-link "
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
        <div class="card-body " contenteditable="true">
        Paragraph lorem ipsum dolor sit amet, bold text consectetur adipiscing elit. Aliquam luctus tristique ligula, italic text eu pretium nunc bold italic text bibendum et. Suspendisse diam dui, gravida fermentum auctor eget, luctus a eros. Link Nunc lacinia nunc ut nulla lacinia, vel porttitor nisl sollicitudin. bold link Cras ut orci porta, placerat nibh sed, iaculis purus. italic link Mauris eros augue, tristique at ante eget, hendrerit mattis erat. bold italic link Cras elit nisi, scelerisque nec tincidunt pellentesque, pretium id urna. 
        </div>
      </div>
    </div>

    <!-- Accordion Item 2 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center" id="headingTwo_<?php echo $accordionid; ?>">
        <h2 class="mb-0 flex-grow-1">
          <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
           data-target="#collapseTwo_<?php echo $accordionid; ?>" aria-expanded="false"
             aria-controls="collapseTwo_<?php echo $accordionid; ?>">
            <span class="accordion-title"
                  data-title-id="title2_<?php echo $accordionid; ?>">Accordion Item #2</span>
          </button>
        </h2>
      </div>
      <div id="collapseTwo_<?php echo $accordionid; ?>" class="collapse"
           aria-labelledby="headingTwo_<?php echo $accordionid; ?>"
           data-parent="#<?php echo $accordionid; ?>">
        <div class="card-body mceEditable" contenteditable="true">
           Paragraph lorem ipsum dolor sit amet, bold text consectetur adipiscing elit. Aliquam luctus tristique ligula, italic text eu pretium nunc bold italic text bibendum et. Suspendisse diam dui, gravida fermentum auctor eget, luctus a eros. Link Nunc lacinia nunc ut nulla lacinia, vel porttitor nisl sollicitudin. bold link Cras ut orci porta, placerat nibh sed, iaculis purus. italic link Mauris eros augue, tristique at ante eget, hendrerit mattis erat. bold italic link Cras elit nisi, scelerisque nec tincidunt pellentesque, pretium id urna. 
        </div>
      </div>
    </div>

    <!-- Accordion Item 3 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center" id="headingThree_<?php echo $accordionid; ?>">
        <h2 class="mb-0 flex-grow-1">
          <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
             data-target="#collapseThree_<?php echo $accordionid; ?>" aria-expanded="false"
             aria-controls="collapseThree_<?php echo $accordionid; ?>">
            <span class="accordion-title"
                  data-title-id="title3_<?php echo $accordionid; ?>">Accordion Item #3</span>
          </button>
        </h2>
      </div>
      <div id="collapseThree_<?php echo $accordionid; ?>" class="collapse"
           aria-labelledby="headingThree_<?php echo $accordionid; ?>"
           data-parent="#<?php echo $accordionid; ?>">
        <div class="card-body mceEditable" contenteditable="true">
            Paragraph lorem ipsum dolor sit amet, bold text consectetur adipiscing elit. Aliquam luctus tristique ligula, italic text eu pretium nunc bold italic text bibendum et. Suspendisse diam dui, gravida fermentum auctor eget, luctus a eros. Link Nunc lacinia nunc ut nulla lacinia, vel porttitor nisl sollicitudin. bold link Cras ut orci porta, placerat nibh sed, iaculis purus. italic link Mauris eros augue, tristique at ante eget, hendrerit mattis erat. bold italic link Cras elit nisi, scelerisque nec tincidunt pellentesque, pretium id urna. 
        </div>
      </div>
    </div>

  </div>
</div>
