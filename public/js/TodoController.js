/**
 * TodoController
 * Created by Oleg on 14.06.2016.
 */

app.controller('TodoController', function ($auth, $state, $http, $rootScope,$scope) {

    $scope.todos = [];
    $scope.newTodo = {};
    
    $scope.init = function () {

        $http.get('/api/todo').success(function (data) {
            $scope.todos = data;
        })
    };

    $scope.save = function () {
        $http.post('/api/todo',$scope.newTodo).success(function (data) {
            $scope.todos.push(data);
            $scope.newTodo = {};
        })
    };

    $scope.update = function (index) {
      $http.put('/api/todo/'+$scope.todos[index].id, $scope.todos[index]);
    };

    $scope.delete = function (index) {
        $http.delete('/api/todo/'+$scope.todos[index].id).success(function () {
            $scope.todos.splice(index, 1);
        });
    };

    $scope.logout = function() {
        $auth.logout().then(function() {
            $rootScope.currentUser = null;
        });
    }

    $scope.init();

});