var backend = 'http://192.168.0.132/teltonikatest';

angular.module( 'sample', [
  'sample.home',
  'sample.login',
  'sample.forgot',
  'sample.reset',
  'angular-jwt',
  'angular-storage'
])
.config( function myAppConfig ($locationProvider, $urlRouterProvider, $stateProvider, jwtInterceptorProvider, $httpProvider) {

  $urlRouterProvider.otherwise(function($injector, $location) {
      console.log("Could not find " + $location.absUrl());
      $location.path('/');
  });

  jwtInterceptorProvider.tokenGetter = function(store) {
    return store.get('jwt');
  }

  $locationProvider.html5Mode({
      enabled: true,
      requireBase: false
  });

  $httpProvider.defaults.withCredentials = true;
  $httpProvider.interceptors.push('jwtInterceptor');
})
.run(function($rootScope, $state, store, jwtHelper) {

  $rootScope.backend = backend;

  $rootScope.$on('$stateChangeStart', function(e, to) {
    if (to.data && to.data.requiresLogin) {
      if (!store.get('jwt') || jwtHelper.isTokenExpired(store.get('jwt'))) {
        e.preventDefault();
        $state.go('login');
      }
    }
  });
})
.controller( 'AppCtrl', function AppCtrl ( $scope, $location ) {
  $scope.$on('$routeChangeSuccess', function(e, nextRoute){
    if ( nextRoute.$$route && angular.isDefined( nextRoute.$$route.pageTitle ) ) {
      $scope.pageTitle = nextRoute.$$route.pageTitle + ' | ngEurope Sample' ;
    }
  });
})

;
