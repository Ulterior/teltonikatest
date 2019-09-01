angular.module( 'sample.reset', [
  'ui.router'
])
.config(function($stateProvider) {
  $stateProvider.state('reset', {
    url: '/reset?:token',
    controller: 'ResetCtrl',
    templateUrl: 'reset/reset.html'
  });
})
.controller( 'ResetCtrl', function ResetController( $scope, $rootScope, $http, store, $state, $stateParams) {

  $scope.user = {'token': $stateParams.token};

  $scope.resetPassword = function() {
    $http({
      url: $rootScope.backend+'/auth/reset',
      method: 'POST',
      data: $scope.user
    }).then(function(response) {
      $state.go('login');
    }, function(error) {
      alert(error.data.error);
    });
  }

});
