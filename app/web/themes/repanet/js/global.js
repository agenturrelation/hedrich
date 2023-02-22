/**
 * @file
 * Global utilities.
 * https://splidejs.com/options/
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.repanet = {
    attach: function (context, settings) {

      // Background image slider.
      $('#img-slider', context).once().each(function () {
        if ($('#img-slider', context).find('li').length > 1) {
          new Splide('#img-slider', {
            // cover: true,
            pauseOnHover: false,
            width: '100vw',
            autoHeight: true,
            autoWidth: true,
            type: 'fade',
            pagination: false,
            autoplay: true,
            interval: 8000,
            speed: 3000,
            perPage: 1,
            rewind: true
          }).mount();
        }
      });

      // Schadenform: Remove hash after closing modal.
      $('#modal-block-damageform', context).once().each(function () {
        $('#modal-block-damageform').on('hide.bs.modal', function (event) {
          if (window.location.hash) {
            history.pushState("", document.title, window.location.pathname
              + window.location.search);
          }
        });
      });
    }
  };

})(jQuery, Drupal);
