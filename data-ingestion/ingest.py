import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import os
import re
import xlrd

pd.set_option('display.max_columns', None)

### reading the file 
#file = pd.read_excel('data.xlsx',error_bad_lines=False)
file = pd.read_csv('data.csv',error_bad_lines=False)

def braintree_file(df):
	### getting the required columns
	col_req = ['Transaction ID','Transaction Type','Transaction Status','Settlement Date','Disbursement Date'
			   ,'Amount Authorized','Settlement Amount','Refunded Transaction ID'
			   ,'Payment Instrument Type','Card Type','Credit Card Number'
			   ,'Billing Postal Code','Billing Country','Shipping Postal Code','Shipping Country'
			   ,'IP Address','Processor Response Code','Settlement Currency ISO Code']
	df = df[col_req]
	
	### renaming the required columns
	col_rename = ['Txn_Id','Txn_Type','Txn_Status','Sttlmnt_Dt','Disbursement_Dt'
				  ,'Auth_Amt','Settled_Amt','Refund_Txn_Id'
				  ,'Payment_Type','Card_Type','CC_Number'
				  ,'Billing_Postal_Code','Billing_Country','Shipping_Postal_Code','Shipping_Country'
				  ,'IP_Addr','Processor_Response_Code','Settlement_Currency']
	df.columns = col_rename
	
	### function to generate insert statements
	#load_statement(df)
	return df

def load_statement(modif_df):
	for index,row in modif_df.iterrows():
		# Make sure all "NaN"s and "NaT"s are wrapped in quotes
		values = str(tuple(row.values))

		# Don't append any empty rows to the dataset
		if "numpy.datetime64('NaT')" in values:
			continue

		values = values.replace( 'NaT', 'null')
		values = values.replace( 'nan', '\'\'')

		sql_state.append('INSERT INTO '+table+' ('+ str(', '.join(modif_df.columns))+ ') VALUES ' + values)

	# Add create table statement
	table_setup = "DROP TABLE IF EXISTS tx_data;\
	CREATE TABLE tx_data(\
		Txn_Id VARCHAR(24),\
		Txn_Type VARCHAR(24),\
		Txn_Status VARCHAR(24), \
		Sttlmnt_Dt DATETIME,\
		Disbursement_Dt DATETIME,\
		Auth_Amt FLOAT,\
		Settled_Amt FLOAT,\
		Refund_Txn_Id VARCHAR(24),\
		Payment_Type VARCHAR(24),\
		Card_Type VARCHAR(24),\
		CC_Number VARCHAR(24),\
		Billing_Postal_Code VARCHAR(24),\
		Billing_Country VARCHAR(24),\
		Shipping_Postal_Code VARCHAR(24),\
		Shipping_Country VARCHAR(24),\
		IP_Addr VARCHAR(24),\
		Processor_Response_Code FLOAT,\
		Settlement_Currency VARCHAR(24),\
		PRIMARY KEY ( Txn_Id )\
	)\
	"

	sql_state.insert(0, table_setup)

	out_file = open( 'out.sql', 'w' )
	out_file.write( ';\n'.join( sql_state ) )

### calling the function to transform the file
sql_state=[]
table ='tx_data'
updated_file = braintree_file(file)

### calling the function to generate insert statement for the file
load_statement(updated_file)
print 'success'