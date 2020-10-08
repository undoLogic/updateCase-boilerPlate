# Variables
SSH_HOST=$(grep '^ *"SSH_HOST":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SSH_USER=$(grep '^ *"SSH_USER":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_CURRENT_BRANCH=$(grep '^ *"GITHUB_CURRENT_BRANCH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_USERNAME=$(grep '^ *"GITHUB_USERNAME":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_PASSWORD=$(grep '^ *"GITHUB_PASSWORD":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_USER_SLASH_PROJECT=$(grep '^ *"GITHUB_USER_SLASH_PROJECT":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SERVER_TMP_LOCATION_ABSOLUTE_PATH=$(grep '^ *"SERVER_TMP_LOCATION_ABSOLUTE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SRC_FILES_RELATIVE_PATH=$(grep '^ *"SRC_FILES_RELATIVE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_URL=$(grep '^ *"TESTING_URL":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_ABSOLUTE_PATH=$(grep '^ *"TESTING_ABSOLUTE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
LIVE_URL=$(grep '^ *"LIVE_URL":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
LIVE_ABSOLUTE_PATH=$(grep '^ *"LIVE_ABSOLUTE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
BROWSER_LOCAL_PATH_WITH_PROGRAM=$(grep '^ *"BROWSER_LOCAL_PATH_WITH_PROGRAM":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
#
#
# Setup the testing server
ssh $SSH_USER@$SSH_HOST "cd $TESTING_ABSOLUTE_PATH && rm -rf $GITHUB_CURRENT_BRANCH && svn export --force --no-auth-cache --username $GITHUB_USERNAME --password $GITHUB_PASSWORD https://@github.com/$GITHUB_USER_SLASH_PROJECT/$GITHUB_CURRENT_BRANCH $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH && rsync -av --omit-dir-times --no-perms $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH/$SRC_FILES_RELATIVE_PATH/. $LIVE_ABSOLUTE_PATH/." && echo ""
#open firefox new tab with link
"C:\Program Files\Firefox Developer Edition\firefox.exe" -new-tab $LIVE_URL