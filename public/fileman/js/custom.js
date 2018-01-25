
function FileSelected(file)
{
    /**
    * file is an object containing following properties:
    * 
    * fullPath - path to the file - absolute from your site root
    * path - directory in which the file is located - absolute from your site root
    * size - size of the file in bytes
    * time - timestamo of last modification
    * name - file name
    * ext - file extension
    * width - if the file is image, this will be the width of the original image, 0 otherwise
    * height - if the file is image, this will be the height of the original image, 0 otherwise
    * 
    */

    var fieldId = RoxyUtils.GetUrlParam('txtFieldId');
    $(window.parent.document).find('#' + fieldId).attr('value', file.fullPath);
    window.parent.closeMediaBrowser();
}

