angular.module( 'sample.forgot', [
  'ui.router'
])
.config(function($stateProvider) {
  $stateProvider.state('forgot', {
    url: '/forgot',
    controller: 'ForgotCtrl',
    templateUrl: 'forgot/forgot.html'
  });
})
.controller( 'ForgotCtrl', function ForgotController( $scope, $rootScope, $http, store, $state) {

  $scope.user = {};

  $scope.forgotPassword = function() {
    $http({
      url: $rootScope.backend+'/auth/forgot',
      method: 'POST',
      data: $scope.user
    }).then(function(response) {
      $state.go('login');
    }, function(error) {
      alert(error.data.error);
    });
  }

});
