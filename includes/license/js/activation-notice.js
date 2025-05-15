jQuery(document).ready(function($) {
    // Access the localized data
    var pluginData = window.tangible_plugin_data_gh;
    
    if (pluginData) {
        // Use the array directly
        var pluginFiles = Array.isArray(pluginData) ? pluginData : pluginData.split(',');

        // Add update class to each plugin row
        pluginFiles.forEach(function(pluginFile) {
          console.log(pluginFile);
            $('tr[data-plugin="' + pluginFile.trim() + '"]').addClass('update');
        });
    }
});
