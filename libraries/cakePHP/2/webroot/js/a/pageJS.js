var WEBROOT = document.getElementById('webroot').getAttribute('data-name'); //allows to reference the webroot passed from cakePHP

var app = angular.module('myApp', []);
app.controller('pagesCtrl', function ($scope, $http, $window, $location, $interval) { //pagesCtrl can be changed to the name of your app i.e. salesCtrl

    // use strict
    $scope.dealerinit = []; //keep data in this object
    $scope.dealerTextarea = "loading..."; //any variables that are visible in the view can be referenced here
    var PAGE_ID = false; //these variables are only available within the object

    //initial load
    loadPages();


    function loadCommonVars() { //get data set in the view

        //DROPDOWN = $('#DROPDOWNID').find(":selected").val();
        PAGE_ID = $('#variable_id').val();
    }



    $scope.loadPages = function() { //this allows a button in the view to load our function calls in this object
        //alert('loading dealers');
        loadPages();
    };

    function loadPages() {

        loadCommonVars();

        loadingColour();

        var objData = {
            // page: setupSessionSearch('page'),
        };

        let URL = WEBROOT + 'staff/Pages/jsonIndex/'+PAGE_ID+'/';
        console.log(URL);
        $http.post(URL, objData).then(function (response) {

            console.log(response.data);
            $scope.data = response.data;

            loadingComplete();
        });
    }

    /* Loading */
    function loadingColour() {
        $('#entireTable').attr('class', 'table responsive loading');
    }
    function loadingComplete() {
        $('#entireTable').attr('class', 'table responsive loaded');
    }


    //handle getting logged out - requires an action in the Users controller
    var interval = $interval(isLoggedIn, 10000);
    function isLoggedIn() {
        let URL = WEBROOT + 'Users/isLoggedIn/';
        console.log(URL);
        $http.get(URL).then(function (response) {
            console.log(response);
            if (response.data == '440 Login Time-out') {
                alert('You Have been Logged out - Please login again');
                // Simulate an HTTP redirect:
                window.location.replace(WEBROOT+"Users/login");
            }
            //$scope.dealers = response.data;
        });
    }


});


