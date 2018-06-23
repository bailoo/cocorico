# Data Importing

## Instructions
- Store your csv in /web/folder as listing_all.csv
- cd into your root directory

#### Run the following commands
    -  app/console doctrine:schema:update --dump-sql
    -  app/console doctrine:schema:update --force
    -  app/console cache:clear
    -  app/console server:start

- You can change the data fields according to csv file in [CororicoCoreBundle:ListingImportController](/src/Cocorico/CoreBundle/Controller/ListingImportController.php)