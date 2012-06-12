This directory contains files relevant to the manual "Leafing Out" classifier.
Author: Ashley Jin
        CENS
		UCLA 2012

The manual classifier was build in order to gather manual classifications of tweets i.e. relevant or irrelevant. 100 random tweets that were collected with the "leafing out" search term were selected and inserted into a databse. 6 people were chosen to manually read each tweet and classify whether it was relevant, irrelevant, or inappropriate. Each person was required to rate each tweet twice for consistency purposes. 

This was not intended to scale or be widely distributed and used.

File Breakdown:
- authenticate.php: authenticate user with username/password combination by checking against database
- completed.html: shown to user when they were done rating each tweet twice
- db_accessor: contains numerous functions for accessing database tables
- home.php: user home page; shows table of tweets with options for ratings
- index.php: login page
- logout.php: logout script
- thankyou.html: shown after a user rates a set of tweets


Known/Open Issues:
- not secure
- session may time out without notifying user
- is ugly
- could flow better
