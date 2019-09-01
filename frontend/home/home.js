angular.module( 'sample.home', [
  'ui.router',
  'angular-storage',
  'angular-jwt'
])
.config(function($stateProvider) {
  $stateProvider.state('home', {
    url: '/',
    controller: 'HomeCtrl',
    templateUrl: 'home/home.html',
    data: {
      requiresLogin: true
    }
  });
})
.controller( 'HomeCtrl', function HomeController( $scope, $rootScope, $state, $http, store, jwtHelper) {

  $scope.todo = {};
  $scope.jwt = store.get('jwt');
  $scope.decodedJwt = $scope.jwt && jwtHelper.decodeToken($scope.jwt);

  $scope.showJwt = function() {
    $scope.view = 'jwt';
  }

  $scope.showTodos = function(user_id) {
    callApi('user_todos', $rootScope.backend+'/admin/todos/'+user_id);
  }

  $scope.callUsers = function() {
    callApi('users', $rootScope.backend+'/admin/users');
  }

  $scope.callSyslogs = function() {
    callApi('syslogs', $rootScope.backend+'/admin/syslogs');
  }

  $scope.showAddTodo = function() {
    $scope.todo = {};
    $scope.view = 'add_todo';
  }

  $scope.myTodos = function() {
    callApi('my_todos', $rootScope.backend+'/todos');
  }

  $scope.callAllTodos = function() {
    callApi('todos', $rootScope.backend+'/admin/todos');
  }

  $scope.addTodo = function() {
    $http({
      url: $rootScope.backend+'/todos',
      method: 'POST',
      data: $scope.todo
    }).then(function(quote) {
      $scope.myTodos();
    }, function(error) {
      alert(error.data.error);
    });
  }

  $scope.deleteUserTodo = function(user_id, todo_id) {
    $http({
      url: $rootScope.backend+'/todos/'+todo_id,
      method: 'DELETE',
    }).then(function(quote) {
      if(user_id)
        $scope.showTodos(user_id);
      else
        $scope.callAllTodos();
    }, function(error) {
      alert(error.data.error);
    });
  }

  $scope.deleteTodo = function(todo_id) {
    $http({
      url: $rootScope.backend+'/todos/'+todo_id,
      method: 'DELETE',
    }).then(function(quote) {
      $scope.myTodos();
    }, function(error) {
      alert(error.data.error);
    });
  }

  $scope.showEditTodo = function(todo_id) {
    $http({
      url: $rootScope.backend+'/todos/'+todo_id,
      method: 'GET',
    }).then(function(todo) {
      $scope.view = 'edit_todo';
      $scope.todo = todo.data;
    }, function(error) {
      alert(error.data.error);
    });
  }

  $scope.editTodo = function(todo_id) {
    $http({
      url: $rootScope.backend+'/todos/'+todo_id,
      method: 'PUT',
      data: $scope.todo
    }).then(function(quote) {
      $scope.myTodos();
    }, function(error) {
      alert(error.data.error);
    });
  }

  function callApi(type, url) {
    $scope.response = null;
    $scope.view = type;
    $http({
      url: url,
      method: 'GET'
    }).then(function(quote) {
      $scope.response = quote.data;
    }, function(error) {
      $scope.response = error.data.error;
    });
  }

  if($scope.decodedJwt.roles.includes('admin'))
    $scope.callUsers();
  else
    $scope.myTodos();
});
