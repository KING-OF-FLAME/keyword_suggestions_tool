$(document).ready(function () {
    $("#submit").click(function () {
        var keyword = $("#keyword").val();
        if (!keyword) {
            console.error("No keyword entered.");
            $("#suggestions").html("<p>Please enter a keyword.</p>");
            return;
        }

        $("#loading").removeClass("hidden");

        $.ajax({
            url: "php/suggestions.php",
            method: "GET",
            data: { keyword: keyword, limit: 10 },
            success: function (response) {
                $("#loading").addClass("hidden");

                if (response.error) {
                    console.error("Error in response:", response.error);
                    $("#suggestions").html("<p>Error: " + response.error + "</p>");
                    return;
                }

                function formatSuggestionsForDisplay(suggestions) {
                    return suggestions.map((suggestion, index) => "<p>" + (index + 1) + ". " + suggestion + "</p>").join("");
                }

                function formatSuggestionsForCopy(suggestions) {
                    return suggestions.map((suggestion, index) => (index + 1) + ". " + suggestion).join("\n");
                }

                function displaySuggestions(containerId, suggestions) {
                    const formattedHtml = formatSuggestionsForDisplay(suggestions);
                    $(containerId).html(formattedHtml);
                }

                displaySuggestions("#google_suggestions", response.google_suggestions);
                displaySuggestions("#youtube_suggestions", response.youtube_suggestions);
                displaySuggestions("#common_suggestions", response.common_keywords);

                function copyToClipboard(text) {
                    var tempInput = $("<textarea>");
                    $("body").append(tempInput);
                    tempInput.val(text).select();
                    document.execCommand("copy");
                    tempInput.remove();
                }

                $("#copyGoogle").off().click(function () {
                    copyToClipboard(formatSuggestionsForCopy(response.google_suggestions));
                    alert("Google Suggestions copied to clipboard!");
                });

                $("#copyYouTube").off().click(function () {
                    copyToClipboard(formatSuggestionsForCopy(response.youtube_suggestions));
                    alert("YouTube Suggestions copied to clipboard!");
                });

                $("#copyCommon").off().click(function () {
                    copyToClipboard(formatSuggestionsForCopy(response.common_keywords));
                    alert("Common Suggestions copied to clipboard!");
                });
            },
            error: function (xhr, textStatus, errorThrown) {
                $("#loading").addClass("hidden");
                console.error("AJAX Error:", textStatus, errorThrown);
                $("#suggestions").html("<p>Error fetching suggestions. Please try again later.</p>");
            }
        });
    });
});
