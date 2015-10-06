(function($) {
  // Disable scroll zooming and bind back the click event
  var onMapMouseleaveHandler = function (event) {
    var that = $(this);
    that.click(onMapClickHandler);
    that.mouseleave(onMapMouseleaveHandler);
    that.children('iframe').css("pointer-events", "none");
  };
  var onMapClickHandler = function (event) {
    var that = $(this);
    // Disable the click handler until the user leaves the map area
    that.click(onMapClickHandler);
    // Enable scrolling zoom
    that.children('iframe').css("pointer-events", "auto");
    // Handle the mouse leave event
    that.mouseleave(onMapMouseleaveHandler);
  };
  // Enable map zooming with mouse scroll when the user clicks the map
  $('.google-map').click(onMapClickHandler);
})(jQuery);
