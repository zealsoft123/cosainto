# Cosainto

Cosainto is a web application platform for score credit card transactions based on risk as well as to track and perform manual investigations against transactions. The funcitonality of the application is split between user-facing functionality and admin-facing functionality.

Users can:
  - Upload a CSV/XLSX file containing a list of credit card transactions into the application to be displayed in their dashboard and scored by the proprietary Cosainto model.
  - Sort the transactions on their dashboard by various metrics including transaction type, transaction amount, transactions status or any of the other columns in their dashboard.
  - Request a manual review by the Cosainto team of any particular transaction that has been imported and scored, along with any manually provided evidence.
  - Pay for the manual review of a given transaction with a credit card directly on the site.
  - Receive updates on a manual investigation when Cosainto has completed their review
  
Admins can:
  - View all transactions through an admin interface, including transaction data, any notes about the transaction and any Cosainto-provided notes as a result of a manual investigation
  - Approve new users to be able to start using the application
  - Provide users with updates to manual investigations once they have been completed
  
## Typical User Flow
  - A user signs up for the application and provides some personal details as well as some information about their company to allow Cosainto to potentially personalize their experience in the future.
  - A user is approved by an administrator and can then log in and access their dashboard
  - A user uploads their first set of transactions in either CSV or XLSX format
  - This data is fed into the risk scoring model, which runs against all transactions and provides a risk score and risk reason for each
  - This data is then imported into the user's dashboard and made accessible to them through the web application
  - A user reviews this data and decides if a manual review of any transactions is necessary.
  - The user uploads any supporting evidence for a given transaction to support the investigation
  - The user requests a manual review for Cosainto
  - The user provides payment for the manual review from right within the app
  - The user's paymment is processed and their transaction information is sent to the Cosainto Salesforce instance where transactions are manually reviewed by the Cosainto team
  - The user sees the updates provided by the Cosainto team as a result of the investigation

# How the Model Runs Inside the Web Application
For an overview of the model, see the graphic below. Each of these steps is explained in further detail below the graphic.

![An overview of the Cosainto Model](https://github.com/cosainto/cosainto/blob/master/model.png)

The individual steps in the model are as follows:

### User uploads their data
A user clicks the 'Upload' button and selects a CSV or XLSX file from their computer. This file is uploaded into temporary storage so that the various pieces of the model can read it.

### Python Script
Cosainto has provided a python script for cleaning/normalizing the data imported from different payment providers that also helps deal with various edge cases like empty data rows/columns. Once the uploaded data file has been moved into its temporary location, the python script is triggered. This script looks at each piece of data in the provided file and converts them to SQL queries that can then be imported into our model database.

### Data import to MySQL
Now that the data is normalized and in the form of MySQL statements, it is imported into a temmporary data table in a MySQL database that has been set up for the purpose of running the model.

### Run the model
After the transactions are imported to MySQL, the Cosainto provided model (in the form of MySQL commands) is run against this newly-imported data. The result of this should be a MySQL table in the temporary database containing a list of the risk scores for each transaction.

### MySQL -> Laravel
These risk scores and risk reasons from the model are mapped back to the other transaction data that Laravel will store and each transaction is now saved in the Laravel database so that the user can see these transactions in their dashboard as well as trigger manual reviews against them and upload additional investigation documentation.

### Redirect
Once the import process is complete and all the transactions are successfully stored in Laravel, the user is redirected to their dashboard, where they can see the transactions that have resulted from their data import and the run of the model.

To update the model, modify the `data-ingestion/ingest.sql` or the `data-investion/ingest.py` file. This can be done in Github and all modifications will be auto-deployed to the staging server 1-2 minutes after commit.

# Updating the model
The model can be updated by checking in changes to the MySQL and/or Python scripts and pushing them to Github. As detailed below, and commit to the `master` branch that is pushed to Github will be automatically deployed. There are some important considerations for each of these file types that will keep the application working properly after every model update.

## Python 
  - When the script imports the CSV/XLSX script, the script needs to check for either data.csv or data.xlsx, which is where Laravel moves the data file on the server. The code snippet should resemble something like this:
  ```
  if( os.path.exists("data.csv") ):
    file_name = 'data.csv'
  else:
    file_name = 'data.xlsx'

  FileType  = str.rsplit(file_name,'.',1)[1]
```
  - At the very end of the file, the script should print `success`. This will never be output to the user, but tells Laravel that the Python script ran successfully and the model process should be allowed to continue.

## MySQL
  - Make sure all `drop table` statements are `drop table if exists`. This will ensure if the database is altered or we decide to start cleaning up tables after model runs in the future there aren't fatal errors from dropping a table that doesn't exist.

# Deploy
All commits to the `master` branch are auto-deployed to staging ([https://cosainto.ap.dev](https://cosainto.ap.dev)) using Github Actions.
