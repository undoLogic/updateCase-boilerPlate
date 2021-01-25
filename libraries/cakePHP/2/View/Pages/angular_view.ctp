<style>
    .loading {
        border: 5px solid orange!important;
        opacity: 0.5;
    }
    .loaded {
        border: 3px solid #89aa8c!important;
        opacity: 1;
    }
    .modified {
        background-color: #c2f1cc;
    }
</style>

<div ng-app="myApp" ng-controller="pagesCtrl">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-12">

                            <?php echo $this->Form->create(); ?>

                            <?= $this->Form->hidden('variable_id', array('type' => 'text')); //allows to move data into the angularJS from here ?>

                            <table class="table responsive" id="entireTable">
                                <thead>

                                <tr>
                                    <th>

                                        Column

                                    </th>

                                </tr>
                                </thead>

                                <tbody >

                                <tr ng-repeat="page in pages" style="border: 3px solid #177dff;" id="page{{dealer.RebateDealer.id}}">
                                    <td data-th="<?= $this->Translate->word('Name'); ?>" width="150">
                                        {{page.name}}
                                    </td>
                                </tr>

                                </tbody>
                            </table>



                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="webroot" data-name="<?= $this->webroot; ?>" src="<?= $this->webroot; ?>js/layouts/a/angularJS.js"></script>
