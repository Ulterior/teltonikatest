angular.module( 'sample.login', [
  'ui.router',
  'angular-storage'
])
.config(function($stateProvider) {
  $stateProvider.state('login', {
    url: '/login',
    controller: 'LoginCtrl',
    templateUrl: 'login/login.html'
  });
})
.controller( 'LoginCtrl', function LoginController( $scope, $rootScope, $http, store, $state) {

  $scope.user = {};

  $scope.login = function() {
    $http({
      url: $rootScope.backend+'/auth/login',
      method: 'POST',
      data: $scope.user
    }).then(function(response) {
      store.set('jwt', response.data.token);
      $state.go('home');
    }, function(error) {
      alert(error.data.error);
    });
  }

});
