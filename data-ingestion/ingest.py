import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import os
import re
import xlrd
pd.set_option('display.max_columns', None)

## suiting based on file needs
if( os.path.exists("data.csv") ):
  file_name = 'data.csv'
else:
  file_name = 'data.xlsx'

FileType  = str.rsplit(file_name,'.',1)[1]


if FileType == 'xlsx':
    file = pd.read_excel(file_name,error_bad_lines=False)
else:
    file = pd.read_csv(file_name,error_bad_lines=False, encoding='ISO-8859-1')

# In[152]:


### passing the file type flow [STRIPE]
def stripe_file_conversion(file):
    
    col_req = ['id','Description','Mode','Created (UTC)'
               ,'Amount','Converted Amount','Amount Refunded'
               ,'Card Funding','Card Brand','Card Last4'
               ,'Card Address Zip', 'Card Address Country','Destination','Destination'
               ,'Card Exp Year','Status','Converted Currency'
              ]


    data = file[col_req] 

    col_rename = ['Txn_Id','Txn_Type','Txn_Status','Sttlmnt_Dt'
                  ,'Auth_Amt','Settled_Amt','Refund_Txn_Id'
                  ,'Payment_Type','Card_Type','CC_Number'
                  ,'Billing_Postal_Code','Billing_Country','Shipping_Postal_Code','Shipping_Country'
                  ,'IP_Addr','Processor_Response_Code','Settlement_Currency']
    
    data.columns = col_rename
    
    data['Sttlmnt_Dt'] = pd.to_datetime(data['Sttlmnt_Dt'])
    #data['Sttlmnt_Dt'] = data['Sttlmnt_Dt'].dt.date    
    
    cat_cols = [x for x in data.dtypes.index if data.dtypes[x] =='object']
    date_cols = [x for x in data.dtypes.index if data.dtypes[x] =='datetime64[ns]']
    float_cols = [x for x in data.dtypes.index if data.dtypes[x] =='float64']

    data[cat_cols] = data[cat_cols].fillna('NA')
    data[date_cols] = data[date_cols].fillna('9999-12-31')
    data[float_cols] = data[float_cols].fillna('0')
    
    data['file_type'] = 'Stripe'
    data['insert_date'] = pd.Timestamp.now()  
    data['merch_id'] = ''

    sql_state=[]
    table ='base_table_temp'

    for index,row in data.iterrows():
        #sql_state.append('INSERT INTO '+table+' ('+ str(', '.join(modif_df.columns))+ ') VALUES ' + str(tuple(row.values)))
        sql_state.append('INSERT INTO '+table+ ' VALUES' + str(tuple(row.values))+';')
    #pd.DataFrame(sql_state).to_csv('insert_file_statement.csv')

    pd.DataFrame(sql_state).to_csv('insert_file_statement.csv',index=False)


# In[153]:


### passing the file type flow [Braintree]
def braintree_file_conversion(file):
    
    ### [BRAINTREE PROCESSING] required columns
    col_req = ['Transaction ID','Transaction Type','Transaction Status','Settlement Date'
               ,'Amount Authorized','Settlement Amount','Refunded Transaction ID'
               ,'Payment Instrument Type','Card Type','Credit Card Number'
               ,'Billing Postal Code','Billing Country','Shipping Postal Code','Shipping Country'
               ,'IP Address','Processor Response Code','Settlement Currency ISO Code']
    data = file[col_req]

    col_rename = ['Txn_Id','Txn_Type','Txn_Status','Sttlmnt_Dt'
                  ,'Auth_Amt','Settled_Amt','Refund_Txn_Id'
                  ,'Payment_Type','Card_Type','CC_Number'
                  ,'Billing_Postal_Code','Billing_Country','Shipping_Postal_Code','Shipping_Country'
                  ,'IP_Addr','Processor_Response_Code','Settlement_Currency']
    data.columns = col_rename
    
    data['Sttlmnt_Dt'] = pd.to_datetime(data['Sttlmnt_Dt'])
    #data['Sttlmnt_Dt'] = data['Sttlmnt_Dt'].dt.date

    cat_cols = [x for x in data.dtypes.index if data.dtypes[x] =='object']
    date_cols = [x for x in data.dtypes.index if data.dtypes[x] =='datetime64[ns]']
    float_cols = [x for x in data.dtypes.index if data.dtypes[x] =='float64']

    data[cat_cols] = data[cat_cols].fillna('NA')
    data[date_cols] = data[date_cols].fillna('9999-12-31')
    data[float_cols] = data[float_cols].fillna('0')
    
    data['file_type'] = 'Braintree'
    data['insert_date'] = pd.Timestamp.now()
    data['merch_id'] = ''
    
    sql_state=[]
    table ='base_table_temp'

    for index,row in data.iterrows():
        sql_state.append('INSERT INTO '+table+ ' VALUES' + str(tuple(row.values))+';')

    pd.DataFrame(sql_state).to_csv('insert_file_statement.csv',index=False)


### passing the file type flow [Adyen]
# adyen cvv column -- CVC2 Response
def adyen_file_conversion(file):    
    
    file['col1'] = ''
    col_req = ['Psp Reference','Type','Acquirer Response','Creation Date'
               ,'Amount','Amount','col1'
               ,'Payment Method','Payment Method','Shopper PAN'
               ,'Billing Postal Code / ZIP','Billing Country','Delivery Postal Code / ZIP','Delivery Country'
               ,'Shopper IP','Authorisation Code','Currency'
              ]    
    
    data = file[col_req]
    
    col_rename = ['Txn_Id','Txn_Type','Txn_Status','Sttlmnt_Dt'
                  ,'Auth_Amt','Settled_Amt','Refund_Txn_Id'
                  ,'Payment_Type','Card_Type','CC_Number'
                  ,'Billing_Postal_Code','Billing_Country','Shipping_Postal_Code','Shipping_Country'
                  ,'IP_Addr','Processor_Response_Code','Settlement_Currency'
                 ]
    
    data.columns = col_rename
    
    data['Sttlmnt_Dt'] = pd.to_datetime(data['Sttlmnt_Dt'])
    #data['Sttlmnt_Dt'] = data['Sttlmnt_Dt'].dt.date    
    
    cat_cols = [x for x in data.dtypes.index if data.dtypes[x] =='object']
    date_cols = [x for x in data.dtypes.index if data.dtypes[x] =='datetime64[ns]']
    float_cols = [x for x in data.dtypes.index if data.dtypes[x] =='float64']

    data[cat_cols] = data[cat_cols].fillna('NA')
    data[date_cols] = data[date_cols].fillna('9999-12-31')
    data[float_cols] = data[float_cols].fillna('0')
    
    data['file_type'] = 'Adyen'
    data['insert_date'] = pd.Timestamp.now()  
    data['merch_id'] = ''

    sql_state=[]
    table ='base_table_temp'

    for index,row in data.iterrows():
        #sql_state.append('INSERT INTO '+table+' ('+ str(', '.join(modif_df.columns))+ ') VALUES ' + str(tuple(row.values)))
        sql_state.append('INSERT INTO '+table+ ' VALUES' + str(tuple(row.values))+';')
    #pd.DataFrame(sql_state).to_csv('insert_file_statement.csv')

    pd.DataFrame(sql_state).to_csv('insert_file_statement.csv',index=False) 

    
### passing the file type flow [Square]
# all txns from SQUARE cc only -- YES /* comment */
def square_file_conversion(file):
    
    file['col1'] = ''
    file['col2'] = ''
    file['col3'] = ''
    file['col4'] = ''
    file['col5'] = ''
    file['col6'] = ''
    file['col7'] = ''
    file['col8'] = ''    
    file['col9'] = ''  
    file['Net Sales'] = file['Net Sales'].str.replace('$', '')
    file['Net Total'] = file['Net Total'].str.replace('$', '')    
    col_req = ['Transaction ID','col1','Transaction Status','Date'
               ,'Gross Sales','Net Total', 'col2'
               ,'Source','Card Brand','PAN Suffix'
               ,'col3','col4','col5','col6'
               ,'col7','col8','col9'
              ]
    
    data = file[col_req]
    
    col_rename = ['Txn_Id','Txn_Type','Txn_Status','Sttlmnt_Dt'
                  ,'Auth_Amt','Settled_Amt','Refund_Txn_Id'
                  ,'Payment_Type','Card_Type','CC_Number'
                  ,'Billing_Postal_Code','Billing_Country','Shipping_Postal_Code','Shipping_Country'
                  ,'IP_Addr','Processor_Response_Code','Settlement_Currency'
                 ]
    
    data.columns = col_rename
    
    data['Sttlmnt_Dt'] = pd.to_datetime(data['Sttlmnt_Dt'])
    #data['Sttlmnt_Dt'] = data['Sttlmnt_Dt'].dt.date    
    
    cat_cols = [x for x in data.dtypes.index if data.dtypes[x] =='object']
    date_cols = [x for x in data.dtypes.index if data.dtypes[x] =='datetime64[ns]']
    float_cols = [x for x in data.dtypes.index if data.dtypes[x] =='float64']

    data[cat_cols] = data[cat_cols].fillna('NA')
    data[date_cols] = data[date_cols].fillna('9999-12-31')
    data[float_cols] = data[float_cols].fillna('0')
    
    data['file_type'] = 'Square'
    data['insert_date'] = pd.Timestamp.now()  
    data['merch_id'] = ''

    sql_state=[]
    table ='base_table_temp'

    for index,row in data.iterrows():
        #sql_state.append('INSERT INTO '+table+' ('+ str(', '.join(modif_df.columns))+ ') VALUES ' + str(tuple(row.values)))
        sql_state.append('INSERT INTO '+table+ ' VALUES' + str(tuple(row.values))+';')
    #pd.DataFrame(sql_state).to_csv('insert_file_statement.csv')

    pd.DataFrame(sql_state).to_csv('insert_file_statement.csv',index=False) 
    
      
### identifying the file type (Braintree or Stripe)
file_type = ''
if (file.columns[0]=='Transaction ID'):
    file_type = 'Braintree'
    braintree_file_conversion(file)
elif file.columns[0]=='id':
    file_type = 'Stripe'
    stripe_file_conversion(file)
elif file.columns[0]=='Date':
    file_type = 'Square'
    square_file_conversion(file) 
elif file.columns[0]=='Company Account':
    file_type = 'Adyen'
    adyen_file_conversion(file)
else:
    file_type = 'Unknown'
