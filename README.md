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

IMAGE GOES HERE

To update the model, modify the `data-ingestion/ingest.sql` or the `data-investion/ingest.py` file. This can be done in Github and all modifications will be auto-deployed to the staging server 1-2 minutes after commit.

# Deploy
All commits to the `master` branch are auto-deployed to staging ([https://cosainto.ap.dev](https://cosainto.ap.dev)) using Github Actions.
