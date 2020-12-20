# figure out which files have changed since git commit / git push origin
# Variables
SSH_HOST=$(grep '^ *"SSH_HOST":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SSH_USER=$(grep '^ *"SSH_USER":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_CURRENT_BRANCH=$(grep '^ *"GITHUB_CURRENT_BRANCH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_USERNAME=$(grep '^ *"GITHUB_USERNAME":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_PASSWORD=$(grep '^ *"GITHUB_PASSWORD":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_USER_SLASH_PROJECT=$(grep '^ *"GITHUB_USER_SLASH_PROJECT":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SRC_FILES_RELATIVE_PATH=$(grep '^ *"SRC_FILES_RELATIVE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_URL=$(grep '^ *"TESTING_URL":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_ABSOLUTE_PATH=$(grep '^ *"TESTING_ABSOLUTE_PATH":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
LIVE_URL=$(grep '^ *"TESTING_URL":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
BROWSER_LOCAL_PATH_WITH_PROGRAM=$(grep '^ *"BROWSER_LOCAL_PATH_WITH_PROGRAM":' settings.json  | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
#
#
#
#GITHUB_CURRENT_BRANCH=$(git branch --show-current)
#GITHUB_CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
#if [ $GITHUB_CURRENT_BRANCH = "master" ]; then
#  GITHUB_CURRENT_BRANCH="trunk"
#else
#  GITHUB_CURRENT_BRANCH="branches/$GITHUB_CURRENT_BRANCH"
#fi

read -p "Not working yet"

#get all changed files
#git diff --name-only > changed-files.txt

echo "rsync -av --files-from=changed-files.txt \"$(git rev-parse --show-toplevel)/.\" $SSH_USER"

read -p "Press enter to continue"

#show command that will be run
echo "rsync -av --files-from=changed-files.txt \"$(git rev-parse --show-toplevel)\"/. $SSH_USER@$SSH_HOST:$TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH/."

read -p "Press enter to continue"

#upload to the server
rsync -av --files-from=changed-files.txt "$(git rev-parse --show-toplevel)"/. $SSH_USER@$SSH_HOST:$TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH/.

read -p "Press enter to continue"

#open firefox new tab with link
#figure out how to pass spaces from the settings page to here as the space is ending the variable
"C:\Program Files\Firefox Developer Edition\firefox.exe" -new-tab $TESTING_URL/$GITHUB_CURRENT_BRANCH/$SRC_FILES_RELATIVE_PATH/