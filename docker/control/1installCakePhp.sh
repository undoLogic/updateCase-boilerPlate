#!/bin/sh
cd /var/www/vhosts/website.com/www
svn export https://github.com/cakephp/cakephp/branches/2.x
mv 2.x src 

#fix the permissions
chmod -R 777 src/app/tmp/

#ensure UpdateCase will be ok
touch src/app/webroot/updateCase.log
chmod 777 src/app/webroot/updateCase.log
chmod 777 src/app/webroot/images/ #probabaly needs to be more secure

# Add layout from purchased template

# add the display code
# echo $this->Flash->render(); ?>
# echo $this->fetch('content'); ?>

# update the baseLayout links
sed -i 's/href=\"c/href=\"<?= $baseLayout; ?>c/g' src/app/View/Layouts/default.ctp
sed -i 's/href=\"i/href=\"<?= $baseLayout; ?>i/g' src/app/View/Layouts/default.ctp
sed -i 's/src=\"i/src=\"<?= $baseLayout; ?>i/g' src/app/View/Layouts/default.ctp
sed -i 's/src=\"j/src=\"<?= $baseLayout; ?>j/g' src/app/View/Layouts/default.ctp
sed -i 's/img="i/img=\"<?= $baseLayout; ?>i/g' src/app/View/Layouts/default.ctp

# ensure the baseLayout exists in the app_controller
# $this->set('baseLayout', $this->webroot.'modules/site/'; ?>