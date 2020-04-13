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
    file = pd.read_csv(file_name,error_bad_lines=False)

# In[152]:


### passing the file type flow
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


### passing the file type flow
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


# In[154]:


### identifying the file type (Braintree or Stripe)
file_type = ''
if (file.columns[0]=='Transaction ID'):
    file_type = 'Braintree'
    braintree_file_conversion(file)
elif file.columns[0]=='id':
    file_type = 'Stripe'
    stripe_file_conversion(file)
else:
    file_type = 'Unknown'
print 'success'

