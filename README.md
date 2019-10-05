# Beer Fest JSON

This is a dataset of Great American Beer Fest (GABF) Winners and World Beer Cup (WBC) Winners.

## Data is from 

- https://www.greatamericanbeerfestival.com/the-competition/winners
- https://www.worldbeercup.org/winners/award-winners

## Using the Scripts

```sh
# Mongo import
php mongo-import.php

# After the winners are announced you can run
php gabf-update.php
php wbc-update.php
```