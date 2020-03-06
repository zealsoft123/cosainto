# Cosainto

# How the Model Runs Inside the Web Application
The model is baked into the application and checks for new transactions every time a user loads their dashboard. The process is as follows:

  - Any transactions without a risk score are pulled into the list of transactions to be scored
  - The base table is created for where the transactions will be stored before the model is run 
```
// Create the base table and base_table_temp
    $base_query = "drop table if exists base_table_temp;
    create table base_table_temp (txn_id varchar(40),
        txn_type varchar(20),
        txn_status varchar(20),
        sttlmnt_dt DATE ,
        auth_amt Decimal (7,2),
        sttlmnt_amt Decimal(7,2),
        refund_txn_id varchar(20),
        payment_type varchar(20),
        card_type varchar(20),
        cc_number varchar(20),
        billing_postal_cd varchar(20),
        billing_country varchar(100),
        shipping_postal_cd varchar(20),
        shipping_country varchar(100),
        ip_addr varchar(20),
        processor_response_code varchar(20),
        sttlmnt_currency varchar(20)
    );";
```
   - `base_table_temp` is populated with the transaction data. Previously this was performed using Python, but to avoid having to have PHP call Python and then run SQL and have a ton of links in the chain, we have brought this into the PHP application.
```
$new_query = "INSERT INTO base_table_temp VALUES('$tx->transaction_id', '$tx->transaction_type',
'$tx->transaction_status', '{$tx->transaction_date->toDateString()}', $amount, $amount, 'NA', 'Credit Card',
'$tx->card_type', '$tx->card_number', '$billing_zipcode', '$billing_country', '$shipping_zipcode',
'$shipping_country', 'NA', 1000.0, 'USD');";

$filtered_queries[] = $new_query;
```
   - All the queries from `ingest.sql` are added to the `$filtered_queries` array.
   - Each of the filtered queries are run in order, to generate all the intermediate database tables necessary to complete the model
   - The risk score that's calcuated is assigned back to each transaction in the cosainto database.
   - The tables and the database created for the model are cleaned up and deleted.
   
For more information and to inspect the code around how this works, you can look at `TransactionController:172` which is where the `sql_split` function that runs the model starts.

To update the model, modify the `data-ingestion/ingest.sql` file. This can be done in Github and all modifications will be auto-deployed to the staging server 1-2 minutes after commit.

# Deploy
All commits to the `master` branch are auto-deployed to staging using Github Actions.
