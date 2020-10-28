# Intro

This is a conversion plugin from jpg/png/bmp to the next-gen webp image format that is required for better perfromances on Google's Page speed insights.

# How to enable automatic conversion and convert current images on the website

1. We need to enable the option to automatically convert images that will be uploaded from the media library
2. Go to: Settings -> Convert Images to Webp -> and enable the option "Enable auto generating images after upload:"
3. Adjust the quality of the webp images (1-100), 90 is optimal to have a good quality and conversion.
4. To convert the previous images that were already uploaded on the website choose the year folders and press "Convert All"
5. You can even convert images that are in the themes folder, the button under convert all: "Convert the Themes Folder"


# How to display the webp images on frontend

We currently use the b-lazy js library to lazy load the images on the website. This plugin has a modified version of b-lazy to load a webp images if the browser supports them. To do this you need to:

1. Add additional data-src to the <img> or <div> called "data-src-webp", to fetch the webp image from an URL you need to use the function: "checkForWebp($url)"
Example:
`
<?php
$image = get_field('hero_image')['sizes']['hero']; //we get the url from the field hero_image with a "hero" image size:
$image_webp = checkForWebp($image); //the server will check if the webp image exists here, will return empty string if there is no image converted in webp
?>
<img class="b-lazy" data-src="<?= $image; ?>" data-src-webp="<?= $image_webp; ?>" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />
`
2. That's it!