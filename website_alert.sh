#!/bin/bash

# set script location as working directory so relative paths are working
cd "$(dirname "$0")"

# get input parameters
email=$1
url=$2
site=$3

# define regex for validation
valid_url='(https?|ftp|file)://[-A-Za-z0-9\+&@#/%?=~_|!:,.;]*[-A-Za-z0-9\+&@#/%=~_|]'
valid_email="^[a-z0-9!#\$%&'*+/=?^_\`{|}~-]+(\.[a-z0-9!#$%&'*+/=?^_\`{|}~-]+)*@([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z0-9]([a-z0-9-]*[a-z0-9])?\$"

# check if input came in the correct order
if [[ $url =~ $valid_email && $email  =~ $valid_url ]]; then

  echo "Please enter the parameters in the correct order (e-maill first, url second)"
  error=1

  else

    # validate url parameter
    if ! [[ $url =~ $valid_url ]]; then
      echo "Please enter a valid URL including http..."
      error=1
    fi

    # validate email parameter
    if ! [[ $email =~ $valid_email ]]; then
      echo "Please enter a valid e-mail address"
      error=1
    fi
fi


if [[ $error == 1 ]]; then

    # if there has been any error, exit with exit code 1 (i.e. error)
    exit 1
    
    else
    
      # crawl a website and save it as current.txt
      wget -O current_$site.txt $url -q

      # compare the last crawl with this one
      diff current_$site.txt before_$site.txt > /dev/null

      # write diff's exit code into a variable
      diff_exit_code=$? 

      # if diff exits with a difference (i.e. exit code 1), send an e-mail
      # otherwise (i.e. exit code 0), do nothing
      if [[ $diff_exit_code == 1 ]]  
      then
	     
	     # send an e-mail alert    
         php -r "mail('$email','$site: UPDATE!!!','There has been an update for $url');"

		 # send a push notification to an iOS device using pushMe by https://pushme.jagcesar.se/ (bash script via https://gist.github.com/JagCesar/94e4a2f91d876d8ae2119f70e12ce1ad)
		 ./pushMe.sh $url
        
      else
	  	
	  	echo null > /dev/null
        #  php -r "mail('$email','$site: sorry, no updates available','no updates at $url.');"

      fi

      # prepare this crawl to be the diffed with in the next rotation
      mv current_$site.txt before_$site.txt      
    
fi
