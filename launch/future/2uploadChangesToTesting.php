<?php

function pr($var) {
    print_r ("\n");
    print_r ($var);
    print_r ("\n");
}

$basePath = exec("echo $(git rev-parse --show-toplevel)");
pr ($basePath);

$settings = file_get_contents('settings.json');
$s = json_decode($settings, true);
$branch = exec("echo $(git rev-parse --abbrev-ref HEAD)");

//#create a list of the changed files
$changedFiles = exec("git diff --name-only ".$basePath.'/src', $out);

pr ($out);

file_put_contents('files_to_upload.txt', implode("\n", $out));

//get the branch
if ($branch == 'master') {
    $GITHUB_CURRENT_BRANCH = 'trunk';
} else {
    $GITHUB_CURRENT_BRANCH = 'branches/'.$branch;
}

$rsync = "rsync -av --files-from=files_to_upload.txt \"$basePath/.\" ". $s['SSH_USER'] ."@" . $s['SSH_HOST'] .":".$s['TESTING_ABSOLUTE_PATH'].'/'.$GITHUB_CURRENT_BRANCH.'/.';

pr ($rsync);

//exec ($rsync);