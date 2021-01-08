# Setup
# Requires deploy keys setup on github
# get the public key from the live server (ssh into the server)
# First time only: (subsequent times this will already be created - to check ls -la and you will see id_rsa*)
# ssh-keygen -t rsa -C "email@undologic.com"
# cd ~/.ssh
# cat id_rsa.pub
# copy that public key
# Github.com -> Settings -> Deploy keys
# Add Deploy Key
# Add comment which server (so you remember later)
# Paste in the key into the box
SSH_HOST=$(grep '^ *"SSH_HOST":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SSH_USER=$(grep '^ *"SSH_USER":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
GITHUB_USER_SLASH_PROJECT=$(grep '^ *"GITHUB_USER_SLASH_PROJECT":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
SRC_FILES_RELATIVE_PATH=$(grep '^ *"SRC_FILES_RELATIVE_PATH":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_URL=$(grep '^ *"TESTING_URL":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
TESTING_ABSOLUTE_PATH=$(grep '^ *"TESTING_ABSOLUTE_PATH":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
LIVE_URL=$(grep '^ *"TESTING_URL":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
BROWSER_LOCAL_PATH_WITH_PROGRAM=$(grep '^ *"BROWSER_LOCAL_PATH_WITH_PROGRAM":' settings.json | awk '{ print $2 }' | sed -e 's/,$//' -e 's/^"//' -e 's/"$//')
#
#
#
GITHUB_CURRENT_BRANCH=$(git branch --show-current)
if [ $GITHUB_CURRENT_BRANCH = "master" ]; then
  GITHUB_CURRENT_BRANCH="master"
else
  GITHUB_CURRENT_BRANCH="branches/$GITHUB_CURRENT_BRANCH"
fi

# echo $SSH_USER@$SSH_HOST "cd $TESTING_ABSOLUTE_PATH && rm -rf $GITHUB_CURRENT_BRANCH && svn export --force --no-auth-cache --username $GITHUB_USERNAME --password $GITHUB_PASSWORD https://@github.com/$GITHUB_USER_SLASH_PROJECT/$GITHUB_CURRENT_BRANCH $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH" && echo ""
echo $SSH_USER@$SSH_HOST "cd $TESTING_ABSOLUTE_PATH && rm -rf $GITHUB_CURRENT_BRANCH && git clone git@github.com:$GITHUB_USER_SLASH_PROJECT.git --branch $GITHUB_CURRENT_BRANCH --single-branch $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH" && echo ""

read -p "Press enter to continue"

#ssh $SSH_USER@$SSH_HOST "cd $TESTING_ABSOLUTE_PATH && rm -rf $GITHUB_CURRENT_BRANCH && svn export --force --no-auth-cache --username $GITHUB_USERNAME --password $GITHUB_PASSWORD https://@github.com/$GITHUB_USER_SLASH_PROJECT/$GITHUB_CURRENT_BRANCH $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH" && echo ""
ssh $SSH_USER@$SSH_HOST "cd $TESTING_ABSOLUTE_PATH && rm -rf $GITHUB_CURRENT_BRANCH && git clone git@github.com:$GITHUB_USER_SLASH_PROJECT.git --branch $GITHUB_CURRENT_BRANCH --single-branch $TESTING_ABSOLUTE_PATH/$GITHUB_CURRENT_BRANCH" && echo ""


#open firefox new tab with link
#figure out how to pass spaces from the settings page to here as the space is ending the variable
"C:\Program Files\Firefox Developer Edition\firefox.exe" -new-tab $TESTING_URL/$GITHUB_CURRENT_BRANCH/$SRC_FILES_RELATIVE_PATH/

read -p "Complete - Press enter to continue"