
/**
 * @file
 * app.js
 */

/**
 * Module creation 'SchadenForm'.
 */
angular.module('SchadenForm', ['ngAnimate', 'ngMessages', 'ui.router', 'ngSanitize', 'ui.bootstrap', 'ngFileUpload'])

  /**
   * Configuration of $httpProvider.
   */
  .config(['$httpProvider', function ($httpProvider) {
      $httpProvider.defaults.useXDomain = true;
      delete $httpProvider.defaults.headers.common['X-Requested-With'];
    }
  ])

  .config(function ($stateProvider, $urlRouterProvider) {

    var url = plugin_base;

    $stateProvider
      .state('form', {
        // url: '/form',
        url: '',
        templateUrl: url + 'app/SchadenForm/form.html',
        controller: 'SchadenFormController'
      })
      .state('form.selection', {
        url: '/selection',
        templateUrl: url + 'app/SchadenForm/form-selection.html',
        resolve: {
          garageData: function(garageService) {
            return garageService.fetchWebservice()
          }
        }
      })
      .state('form.damage', {
        url: '/damage',
        templateUrl: url + 'app/SchadenForm/form-damage.html',
        resolve: {
          garageData: function(garageService) {
            return garageService.fetchWebservice()
          }
        }
      })
      .state('form.request', {
        url: '/request',
        templateUrl: url + 'app/SchadenForm/form-request.html',
        resolve: {
          garageData: function(garageService) {
            return garageService.fetchWebservice()
          }
        }
      })
      .state('form.confirmation', {
        url: '/confirmation',
        templateUrl: url + 'app/SchadenForm/form-confirmation.html',
        resolve: {
          garageData: function(garageService) {
            return garageService.fetchWebservice()
          }
        }
      })
      .state('form.error', {
        url: '/error',
        templateUrl: url + 'app/SchadenForm/form-error.html'
      });

    // catch all route
    // send users to the form page
    // $urlRouterProvider.otherwise('/form/selection');
    $urlRouterProvider.otherwise('');
  })

  /**
   * Configuration of routes ('ui.router).
   */
  /*
  .config(function ($stateProvider, $urlRouterProvider, $locationProvider) {

    var url = plugin_base;

    $stateProvider
      .state('form', {
        url: '',
        templateUrl: url + 'app/SchadenForm/form.html',
        controller: 'SchadenFormController',
        resolve: {
          garageData: function(garageService) {
            return garageService.fetchWebservice()
          }
        }
      })
      .state('form.selection', {
        // url: '/selection',
        templateUrl: url + 'app/SchadenForm/form-selection.html',

      })
      .state('form.damage', {
        // url: '/damage',
        templateUrl: url + 'app/SchadenForm/form-damage.html',
      })
      .state('form.request', {
        // url: '/request',
        templateUrl: url + 'app/SchadenForm/form-request.html',
      })
      .state('form.confirmation', {
        // url: '/confirmation',
        templateUrl: url + 'app/SchadenForm/form-confirmation.html',
      })
      .state('form.error', {
        // url: '/error',
        templateUrl: url + 'app/SchadenForm/form-error.html'
      });

    // catch all route
    // send users to the form page
    $urlRouterProvider.otherwise('');
    // $urlRouterProvider.otherwise('/form.selection');

    // $state.go('form');
  })
  */

  /**
   * Load garage data from webservice.
   */
  .factory('garageService', function($http, $q, $state) {

    var garageService = {};

    // Set has_mechanics.
    var hasMechanics = angular.element(document.getElementById('schadenform-wrapper')).attr('data-hasMechanics');
    garageService.garageData = {
      has_mechanics: !(!hasMechanics || hasMechanics === "0"),
      identifier: "dummy" // not sure if this can be deleted
    };

    garageService.fetchWebservice = function() {
      return garageService.garageData;
    };

    garageService.getGarageData = function() {
      return garageService.garageData;
    };


    return garageService;
  })

  /**
   * Smooth scroll service.
   */
  .service('anchorSmoothScroll', function(){

    this.scrollTo = function(eID) {

      var startY = currentYPosition();
      var stopY = elmYPosition(eID);
      var distance = stopY > startY ? stopY - startY : startY - stopY;
      if (distance < 100) {
        scrollTo(0, stopY); return;
      }
      var speed = Math.round(distance / 100);
      if (speed >= 20) speed = 20;
      var step = Math.round(distance / 25);
      var leapY = stopY > startY ? startY + step : startY - step;
      var timer = 0;
      if (stopY > startY) {
        for ( var i=startY; i<stopY; i+=step ) {
          setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
          leapY += step; if (leapY > stopY) leapY = stopY; timer++;
        } return;
      }
      for ( var i=startY; i>stopY; i-=step ) {
        setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
        leapY -= step; if (leapY < stopY) leapY = stopY; timer++;
      }

      function currentYPosition() {
        // Firefox, Chrome, Opera, Safari
        if (self.pageYOffset) return self.pageYOffset;
        // Internet Explorer 6 - standards mode
        if (document.documentElement && document.documentElement.scrollTop)
          return document.documentElement.scrollTop;
        // Internet Explorer 6, 7 and 8
        if (document.body.scrollTop) return document.body.scrollTop;
        return 0;
      }

      function elmYPosition(eID) {
        var elm = document.getElementById(eID);
        var y = elm.offsetTop;
        var node = elm;
        while (node.offsetParent && node.offsetParent !== document.body) {
          node = node.offsetParent;
          y += node.offsetTop;
        } return y;
      }

    };
  })


  /**
   * The controller 'SchadenFormController'.
   */
  .controller('SchadenFormController', function ($scope, garageService, $state, $http, $sce, Upload, $timeout, anchorSmoothScroll, $window) {

    /**
     * Reset the form data.
     */
    $scope.resetFormData = function() {

      $scope.formData = {
        formType: '',
        damage: {
          damageFormType: 'body-paint',
          carParts: {},
          carSpecs: {},
          service: 'self'
        },
        request: {},
        contactData: {},
        fileUploads: {}
      };
      /*
      $scope.formData.request.message = 'Das ist ein Test';
      $scope.formData.contactData = {
        firstname: 'Max',
        lastname: 'Mustermann',
        street: 'Musterstr.',
        postalcode: '1234',
        city: 'Musterstadt',
        phone: '0172-12345',
        email: 'guendert@nettags.de',
      }
      */
    };

    $scope.pluginBase = plugin_base;
    $scope.baseUrl = base_url;
    $scope.currentState = $state.current.name;

    $scope.resetFormData();
    $scope.garageData = garageService.getGarageData();
    $scope.formSubmitted = false;
    $scope.submitButtonDisabled = false;
    $scope.maxUploadSize = '4MB';

    /**
     * Set the form type.
     */
    $scope.setFormType = function(route) {
      // Set formType on possible direct load on route.
      if(route === 'form.damage') {
        $scope.formData.formType = 'damage';
      }
      else if(route === 'form.request') {
        $scope.formData.formType = 'request';
      }
    };
    // Set formType on possible direct load on route.
    $scope.setFormType($state.current.name);

    /**
     * On route change start.
     */
    $scope.$on('$stateChangeStart',
      function(event, toState, toParams, fromState, fromParams) {
        $scope.setFormType(toState.name);
        $scope.formSubmitted = false;
        $scope.submitButtonDisabled = false;
      });

    /**
     * On route end/success.
     */
    $scope.$on('$stateChangeSuccess',
      function(event, toState, toParams, fromState, fromParams){
        if ($state.current.name !== 'form.error') {
          $scope.garageData = garageService.getGarageData();
        }
        if ($state.current.name === 'form.confirmation') {
          $scope.resetFormData();
        }

        // Set current state to read in template.
        $scope.currentState = $state.current.name;
      });

    /**
     * Determinates if validation message shold be displayed.
     */
    $scope.showValidationMessage = function(field) {
      return $scope.formSubmitted || field.$dirty;
    };

    /**
     * Set the damage form type.
     */
    $scope.setDamageFormType = function(damageFormType) {
      $scope.formData.damage.damageFormType = damageFormType;
      if ($window.innerWidth < 550) {
        // Smooth scroll to form on mobile.
        setTimeout(function() {
          anchorSmoothScroll.scrollTo('form-begin');
        }, 50);
      }
    };

    /**
     * ng-class callback for damage form type.
     */
    $scope.isDamageFormTypeActive = function(damageFormType) {
      return ($scope.formData.damage.damageFormType === damageFormType);
    };

    /**
     * Set car damage car part.
     */
    $scope.setCarDamagePart = function(partID, partName) {
      if ($scope.formData.damage.carParts[partID] === undefined) {
        $scope.formData.damage.carParts[partID] = partName;
      }
      else {
        delete $scope.formData.damage.carParts[partID];
      }
    };

    /**
     * Sets mechanics fields as required if option 'mechanics' is selected
     * in form type 'request'.
     */
    $scope.isCarspecsFieldRequired = function() {
      return true;
      // return ($scope.formData.request.requestType == 'mechanics');
    };

    /**
     * Sets message field as required if option 'mechanics' is selected
     * in form type 'damage'.
     */
    $scope.isDamageMessageFieldRequired = function() {
      return ($scope.formData.damage.damageFormType === 'mechanics');
    };

    /**
     * ng-class callback for damage car part.
     */
    $scope.isCarDamagePartActive = function(partID) {
      return ($scope.formData.damage.carParts[partID] !== undefined);
    };

    /**
     * Toogle MechanicsAdditional fieldset.
     */
    $scope.showCarspecsAdditionalFieldset = false;
    $scope.toggleCarspecsAdditionalFieldset = function() {
      $scope.showCarspecsAdditionalFieldset = $scope.showCarspecsAdditionalFieldset === false;
    };

    /**
     * Returns the template url based on $scope.pluginBase.
     */
    $scope.getTemplateUrl = function(filename) {
      return $sce.trustAsResourceUrl($scope.pluginBase + filename);
    };

    /**
     * Upload of file, from html file field.
     */
    $scope.uploadFile = function(file, fieldName) {
      if (file != null && file.$error === undefined) {
        $scope.submitButtonDisabled = true;
        file.upload = Upload.upload({
          url: $scope.baseUrl + '/damage_form/upload_image',
          method: 'POST',
          file: file,
          fileFormDataName: 'image_upload'
        });

        file.upload.then(function (response) {
          $timeout(function () {
            if (response.data.success !== undefined) {
              file.result = response.data.data;
              $scope.formData.fileUploads[fieldName] = file.result;
              // Set the validation of the field manually to true.
              $scope.schadenForm[fieldName].$setValidity("maxSize", true);
            }
          });
        }, function (response) {
          if (response.status > 0)
            $scope.errorMsg = response.status + ': ' + response.data;
        });

        file.upload.progress(function (evt) {
          // Math.min is to fix IE which reports 200% sometimes
          file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
        });

        $scope.submitButtonDisabled = false;
      }
    };

    /**
     * Form submit handler.
     */
    $scope.submitForm = function (isFormValid) {
      $scope.formSubmitted = true;
      if(isFormValid) {
        $scope.submitButtonDisabled = true;
          $http({
            method : 'POST',
            url: $scope.baseUrl + '/damage_form/submit',
            params : $scope.formData
          }).success(function(data){
            if (data.success) {
              $scope.resultMessage = data.message;
              $state.go('form.confirmation');
            }
            else {
              $scope.submitButtonDisabled = false;
              $state.go('form.error');
            }
          }).error(function(error) {
            $scope.submitButtonDisabled = false;
            $state.go('form.error');
          });
      }
    };

  });

angular.element(document).ready(function() {
  angular.bootstrap(document.getElementById("schadenform-wrapper"), ['SchadenForm']);
});
