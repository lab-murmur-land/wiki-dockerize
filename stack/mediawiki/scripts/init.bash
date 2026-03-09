#!/bin/bash

php maintenance/run.php installPreConfigured
php maintenance/run.php createAndPromote --sysop --bureaucrat "AdminKullaniciAdin" "AdminSifren123"
