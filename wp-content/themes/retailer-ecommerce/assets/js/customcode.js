/*===============================================
Color change js
=============================================== */

jQuery(document).ready(function() {
    jQuery("h2.heading-banner").each(function() {
        var text = jQuery(this).text().trim();
        
        // Split the text into words
        var words = text.split(" ");
        var len = words.length;

        // Check if there are at least two words to wrap
        if (len >= 2) {
            // Extract the second-to-last word and wrap it
            var secondLastWord = words[len - 2];
            var wrappedSecondLastWord = "<span class='color-word'>" + secondLastWord + "</span>";
            
            // Replace the second-to-last word with the wrapped version
            words[len - 2] = wrappedSecondLastWord;
            
            // Join the words back into a single string
            var newText = words.join(" ");
            
            // Set the new HTML content
            jQuery(this).html(newText);
        }
    });
});