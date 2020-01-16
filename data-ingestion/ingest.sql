/*
--- importing the file 
drop table if exists cosainto_data.rt_import_table;
create table cosainto_data.rt_import_table
(
    bt_txn_id varchar(20),
    merch_id varchar(20),
    txn_dt DATE FORMAT 'YYYY-MM-DD',
    txn_stlmnt_dt DATE FORMAT 'YYYY-MM-DD'
)primary index(merch_id);

--- importing CB file
drop table if exists cosainto_data.rt_import_cb;
create table cosainto_data.rt_import_cb
(
    

)primary index(merch_id);

--- importing Decline file
drop table if exists cosainto_data.rt_import_decline;
create table cosainto_data.rt_import_decline
(


)primary index(merch_id);
*/


-- ----- Base table
drop table if exists cosainto_data.rt_base_table;
create table cosainto_data.rt_base_table (PRIMARY KEY(txn_id)) as
(
    select
        cast('unknown' as char(24)) as merch_id,
        txn_id,
        CC_Number as cr_card_num,
        Settled_Amt as txn_amt,
        Sttlmnt_Dt as txn_stlmnt_dt,
        cast('NA' as char(24) ) as region
        from cosainto_data.tx_data
        where Txn_Status = 'settled'
);

-- ----- Creating rollups at card level [summary of TPV, Txn count based on region and bin]
drop table if exists cosainto_data.rt_card_rollups;
create table cosainto_data.rt_card_rollups as
(
    select
        merch_id,
        txn_stlmnt_dt,
        substr(cr_card_num,1,6) as cr_card_bin,
        max(txn_amt) as max_bin_card_txn,
        min(txn_amt) as min_bin_card_txn,
        count(txn_id) as total_bin_txns,
        sum(txn_amt) as total_bin_txns_amt
    from cosainto_data.rt_base_table 
    group by 1,2,3
);

-- ------- Creating rollups at merchant level
drop table if exists `rt_base_rollups`;
create table cosainto_data.rt_base_rollups as
(
    select
        merch_id,
        region,
        txn_stlmnt_dt,
        sum(txn_amt) as total_amt,
        count(txn_id) as total_txns,
        min(txn_amt) as min_txn_amt,
        max(txn_amt) as max_txn_amt,
        sum(case when txn_amt >= 0 then txn_amt else 0 end) as gtpv,
        sum(case when txn_amt >= 0 then 1 else 0 end) as gtxns,
        sum(case when txn_amt < 0 then txn_amt else 0 end) as refund_amt,
        sum(case when txn_amt < 0 then 1 else 0 end) as refund_txn
    from cosainto_data.rt_base_table
    group by 1,2,3
);

ALTER TABLE `rt_base_rollups` ADD COLUMN `max_min_diff` FLOAT as (`max_txn_amt` - `min_txn_amt`) STORED;
ALTER TABLE `rt_base_rollups` ADD COLUMN `asp` FLOAT as (CASE WHEN gtxns = 0 then 0 else (gtpv / (gtxns * 1.00)) end);

-- ------- [RULE] CONCENTRATED BIN TXNS - X% more txns processed on the same bin [LOGIC]
drop table if exists cosainto_data.rt_concc_summary;
create table cosainto_data.rt_concc_summary (PRIMARY KEY(merch_id)) as 
(
       select  
            base.merch_id, 
            base.cr_card_bin,
            base.total_bin_txns_amt,
            base.total_bin_txns,
            cast(base.total_bin_txns as decimal(10,2)) / total.total_txns * 1.00 as card_txn_ratio
        from cosainto_data.rt_card_rollups base
        join cosainto_data.rt_base_rollups total
        on base.merch_id = total.merch_id
        where total.total_txns > 3
);

drop table if exists cosainto_data.rt_concc_bin_txn;
create table cosainto_data.rt_concc_bin_txn (PRIMARY KEY(merch_id)) as 
(
    select
        merch_id,
        max(card_txn_ratio) as max_card_txn_ratio
        from cosainto_data.rt_concc_summary
    group by 1
);

drop table if exists cosainto_data.rt_concc_flag_amt;
create table cosainto_data.rt_concc_flag_amt (PRIMARY KEY(merch_id)) as 
(
       select  
            base.merch_id,
            max(base.total_bin_txns_amt) as total_bin_txns_amt
        from cosainto_data.rt_concc_summary base
        join cosainto_data.rt_concc_bin_txn total
        on base.merch_id = total.merch_id
        and base.card_txn_ratio = total.max_card_txn_ratio
        group by 1
);

-- ------- [SCORE] CONCENTRATED BIN TXNS - X% more txns processed on the same bin [SCORE]
drop table if exists cosainto_data.rt_concc_bin_score;
create table cosainto_data.rt_concc_bin_score (PRIMARY KEY(merch_id)) as
(
    select
        merch_id,
        case 
            when max_card_txn_ratio >= 0.50 then 'high_concentrated_bin_10'
            when max_card_txn_ratio >= 0.30 then 'high_concentrated_bin_08'
        end as concc_bin_flag
    from cosainto_data.rt_concc_bin_txn     
);

ALTER TABLE `rt_concc_bin_score` ADD COLUMN `concc_bin_score` FLOAT as (case when concc_bin_flag is null then 0 else substr(trim(concc_bin_flag),LENGTH(trim(concc_bin_flag))-1,2) end );

-- ------- [RULE] SAME DOLLAR AMT - 30% or more txns processed reflect the same amount [LOGIC]
drop table if exists cosainto_data.rt_same_dollar_amt;
create table cosainto_data.rt_same_dollar_amt (PRIMARY KEY(merch_id)) as 
(
    select
        merch_id,
        region,
        max(dollar_amt_txn_ratio) as dollar_amt_txn_ratio
       -- dollar_amt_dominate
        from
        (
            select
                t1.merch_id,
                t1.region,
                t1.txn_amt,
                t1.total_dollar_txns / (t2.total_txns * 1.00) as dollar_amt_txn_ratio -- -- dividing total txns happening for a particular price point against total txns 
            from 
                (
                    select  
                        merch_id,
                        region,
                        txn_amt,
                        count(txn_id) as total_dollar_txns  -- this represents total number of transactions happening for that price point
                    from cosainto_data.rt_base_table
                    group by 1,2,3
                ) t1
            join cosainto_data.rt_base_rollups t2
            on t1.merch_id = t2.merch_id
            where t2.total_txns > 3
        ) temp
    group by 1,2
);

-- ------- [RULE] SAME DOLLAR AMT - 30% or more txns processed reflect the same amount [SCORE]
drop table if exists cosainto_data.rt_same_dollar_amt_flag;
create table cosainto_data.rt_same_dollar_amt_flag (PRIMARY KEY(merch_id)) as 
(   
    select
        merch_id,
        case 
            when dollar_amt_txn_ratio >= 0.75 then 'high_same_dollar_amt_txns_08'
            when dollar_amt_txn_ratio >= 0.50 then 'high_same_dollar_amt_txns_05'
        end as dollar_amt_dom_flag
    from cosainto_data.rt_same_dollar_amt  
);

ALTER TABLE `rt_same_dollar_amt_flag` ADD COLUMN `dollar_amt_dom_score` FLOAT as (case when dollar_amt_dom_flag is null then 0 else substr(trim(dollar_amt_dom_flag),LENGTH(trim(dollar_amt_dom_flag))-1,2) end);


-- ------- [RULE] HIGH OUTLIER [****** TRANSACTION LEVEL RULE ******] [LOGIC]
drop table if exists cosainto_data.rt_high_outlier;
create table cosainto_data.rt_high_outlier as 
(
    select 
        base.*,
        'Y' as high_outlier,
        cast('outlier_txn' as char(40)) as reason
        from cosainto_data.rt_base_table base
        join cosainto_data.rt_base_rollups t1
        on base.merch_id = t1.merch_id
        where base.txn_amt > 3 * t1.asp
        and txn_amt > 0 
);

-- ------- [RULE] HIGH OUTLIER [TRANSACTION LEVEL] [SCORE]
drop table if exists cosainto_data.rt_high_outlier_flag;
create table cosainto_data.rt_high_outlier_flag (PRIMARY KEY(merch_id)) as 
(
    select
        merch_id,
        high_outlier
    from cosainto_data.rt_high_outlier
    where high_outlier = 'Y'
    group by 1,2
);

-- ------- [RULE] HIGH SETTLEMENT AMOUNT [LOGIC] [TRANSACTION LEVEL]
drop table if exists cosainto_data.rt_high_amt_sttlmnt;
create table cosainto_data.rt_high_amt_sttlmnt (PRIMARY KEY(merch_id)) as
(
    select
        t1.*
        from 
        (
            select 
                base.merch_id,
                base.txn_id,
                base.cr_card_num,
                base.txn_amt,
                rollups.asp / base.txn_amt * 1.00 as high_amt_asp_ratio, 
                row_number() over(partition by base.merch_id order by txn_amt desc) as rn,
                'Y' as high_stlmnt_amt
            from cosainto_data.rt_base_table base 
            left join cosainto_data.rt_base_rollups rollups
            on base.merch_id = rollups.merch_id
            where base.txn_amt > 100
            and base.txn_amt > 1.5 * asp
        ) t1
    where rn < 3
);

-- ------- [RULE] HIGH SETTLEMENT AMOUNT [SCORE]
drop table if exists cosainto_data.rt_high_amt_sttlmnt_flag;
create table cosainto_data.rt_high_amt_sttlmnt_flag (PRIMARY KEY(merch_id)) as
(
    select
        merch_id,
        case 
            when high_amt_asp_ratio >= 3.0 then 'high_asp_15'
            when high_amt_asp_ratio >= 1.2 then 'high_asp_12'
        end as high_amt_stlmnt_flag,
        case
            when high_amt_stlmnt_flag is null then 0
            else substr(trim(high_amt_stlmnt_flag),LENGTH(trim(high_amt_stlmnt_flag))-1,2)
        end as high_amt_stlmnt_score
    from cosainto_data.rt_high_amt_sttlmnt  
);

-- ------- [RULE] HIGH ASP
drop table if exists cosainto_data.rt_high_asp;
create table cosainto_data.rt_high_asp (PRIMARY KEY(merch_id)) as 
(
    select 
        merch_id,
        case when asp > 500 then 'Y' else 'N' end as high_asp
        from cosainto_data.rt_base_rollups
        where total_txns > 5
        and total_amt > 1000
);

-- ------- [RULE] HIGH SKEWED TXNS [TRANSACTION LEVEL]
drop table if exists cosainto_data.rt_skewed_txns;
create table cosainto_data.rt_skewed_txns (PRIMARY KEY(merch_id)) as
(
    select
        t1.*,
        base.cr_card_num,
        base.txn_amt,
        base.txn_stlmnt_dt,
        base.txn_dt,
        base.region,
        cast('skewed_txns' as char(40)) as reason
        from
        (
            select  
                base.merch_id,
                base.txn_id,
                case when base.txn_amt > median_txn_amt * 10 then 'Y' else 'N' end as high_skewed_txn,
                row_number() over(partition by base.merch_id order by base.txn_amt desc) as rn
                from cosainto_data.rt_base_table base
                join 
                (
                    select
                        merch_id,
                        median(txn_amt) as median_txn_amt
                    from cosainto_data.rt_base_table
                    where txn_amt > 0
                    group by 1 
                ) t2
                on base.merch_id = t2.merch_id
        ) t1
        join cosainto_data.rt_base_table base
        on t1.merch_id = base.merch_id and t1.txn_id = base.txn_id
        where rn < 3
        and high_skewed_txn = 'Y'
);

-- ------- [RULE] HIGH SKEWED TXNS [SCORE]
drop table if exists cosainto_data.rt_skewed_txns_flag;
create table cosainto_data.rt_skewed_txns_flag (PRIMARY KEY(merch_id)) as
(   
    select
        merch_id,
        case 
            when skewed_portfolio_ratio >= 2.0 then 'high_skewed_portfolio_15'
            when skewed_portfolio_ratio >= 1.2 then 'high_skewed_portfolio_12'
        end as skewed_portfolio_flag,
        case
            when skewed_portfolio_flag is null then 0
            else substr(trim(skewed_portfolio_flag),LENGTH(trim(skewed_portfolio_flag))-1,2)
        end as skewed_portfolio_score
        from
        (
            select
                merch_id,
                count(txn_id) as total_txns,
                sum(case when high_skewed_txn = 'Y' then 1 else 0 end) as total_skewed_txns,
                total_skewed_txns / total_txns * 1.00 as skewed_portfolio_ratio
                from cosainto_data.rt_skewed_txns 
                group by 1
        ) t1
);

/*
Formula for normalizing score -> ( (x - min(x))/ (max(x) - min(x)) )
*/

/*
Scope to add
1. Authentic Merchant URL [Y/N]
2. TOF of merchant [if merchant onboarding data is provided]
3. HIGH CB
4. HIGH Declines
5. HIGH Refunds
6. Bad Bin Mapping [if merchant's bin is in bad dataset]
*/

-- ------- [SUMMARY] consolidating the rules table together
drop table if exists cosainto_data.rt_rule_consolidate1;
create table cosainto_data.rt_rule_consolidate1 (PRIMARY KEY(merch_id)) as
(
    select 
        base.merch_id,
        base.total_txns,
        base.total_amt,
        base.asp,
        concc_txn.concc_bin_score,
        dollar_dom.dollar_amt_dom_score,
        case when outlier_flag.high_outlier = 'Y' then 12 else 0 end as high_outlier_score,
        high_amt.high_amt_stlmnt_score,
        case when asp.high_asp = 'Y' then 15 else 0 end as high_asp_score,
        skew_flag.skewed_portfolio_flag
        
    from cosainto_data.rt_base_rollups base 
    left join cosainto_data.rt_concc_bin_score concc_txn
    on base.merch_id = concc_txn.merch_id
    left join cosainto_data.rt_same_dollar_amt_flag dollar_dom
    on base.merch_id = dollar_dom.merch_id
    left join cosainto_data.rt_high_outlier_flag outlier_flag
    on base.merch_id = outlier_flag.merch_id
    left join cosainto_data.rt_high_amt_sttlmnt_flag high_amt
    on base.merch_id = high_amt.merch_id
    left join cosainto_data.rt_high_asp asp
    on base.merch_id = asp.merch_id
    left join cosainto_data.rt_skewed_txns_flag skew_flag
    on base.merch_id = skew_flag.merch_id
);

-- ------- [SCORING] Merchant Level Output
drop table if exists cosainto_data.rt_merch_level_score_agg;
create table cosainto_data.rt_merch_level_score_agg (PRIMARY KEY(merch_id)) as 
(
    select
        merch_id,
        total_txns,
        total_amt,
        asp,
        coalesce(concc_bin_score,0)
            + coalesce(dollar_amt_dom_score,0)
            + coalesce(high_outlier_score,0)
            + coalesce(high_amt_stlmnt_score,0)
            + coalesce(high_asp_score,0)
            + coalesce(skewed_portfolio_flag,0)
        as total_merchant_score
    from cosainto_data.rt_rule_consolidate1
);

update a from cosainto_data.rt_merch_level_score_agg a, cosainto_data.rt_concc_flag_amt b
set
    total_merchant_score = a.total_merchant_score + b.total_bin_txns_amt * 0.05
    where a.merch_id = b.merch_id;


-- ------- [SCORING] Merchant Level Output
drop table if exists cosainto_data.rt_merch_level_score_agg_name;
create table cosainto_data.rt_merch_level_score_agg_name (PRIMARY KEY(merch_id)) as 
(
    select 
        base.merch_id,
        base.total_txns,
        base.total_amt,
        base.asp,
        score.total_merchant_score,
        case when
            concc_bin_score > 0 then 'high_concentrated_card' else ''
            end as RI_CONCC_CARD,
        case when 
            dollar_amt_dom_score > 0 then 'same_dollar_amt_txns' else ''
            end as RI_SAME_DOLLAR_AMT,
        case when 
            high_outlier_score > 0 then 'high_outlier_portfolio' else ''
            end as RI_HIGH_OUTLIER,
        case when 
            high_amt_stlmnt_score > 0 then 'high_amount_settlement' else ''
            end as RI_HIGH_AMT_STLMNT,
        case when 
            high_asp_score > 0 then 'high_asp' else ''
            end as RI_HIGH_ASP,
        case when 
            skewed_portfolio_flag > 0 then 'high_skewed_portfolio' else ''
            end as RI_HIGH_SKEW
    from  cosainto_data.rt_rule_consolidate1 base
    join  cosainto_data.rt_merch_level_score_agg score
    on base.merch_id = score.merch_id       
);

drop table if exists cosainto_data.rt_merch_level_score_stat;
create table cosainto_data.rt_merch_level_score_stat as 
(
    select
        max(total_merchant_score * 1.00) as max_score,
        min(total_merchant_score * 1.00) as min_score,
        max_score - min_score as max_min_diff
    from cosainto_data.rt_merch_level_score_agg_name
);

drop table if exists cosainto_data.rt_merch_level_score_output;
create table cosainto_data.rt_merch_level_score_output (PRIMARY KEY(merch_id)) as 
(
    select
        merch_id,
        total_txns,
        total_amt,
        asp,
        total_merchant_score,
        ltrim(risk_indicators1,'\|') as risk_indicators
        from
            (
            select
                    merch_id,
                    total_txns,
                    total_amt,
                    asp,
                    total_merchant_score,       
                    rtrim(risk_indicators_temp,'\|') as risk_indicators1
                    from
                    (
                        select
                            merch_id,
                            total_txns,
                            total_amt,
                            asp,
                            total_merchant_score,
                            regexp_replace
                            (
                                trim(RI_CONCC_CARD) || '|' ||
                                trim(RI_SAME_DOLLAR_AMT) || '|' ||
                                trim(RI_HIGH_OUTLIER) || '|' ||
                                trim(RI_HIGH_AMT_STLMNT) || '|' ||
                                trim(RI_HIGH_ASP) || '|' ||
                                trim(RI_HIGH_SKEW) 
                            ,'(\|){1,}', '\1'
                            )
                            as risk_indicators_temp
                        from cosainto_data.rt_merch_level_score_agg_name 
                    ) t1
            ) t2
);

-- ---- score standardization
drop table if exists cosainto_data.rt_merch_level_score_final;
create table cosainto_data.rt_merch_level_score_final (PRIMARY KEY(merch_id)) as 
(
    select
        base.merch_id,
        base.total_txns,
        base.total_amt,
        base.asp,
        base.total_merchant_score as org_score,
        ((base.total_merchant_score - st.min_score * 1.00) / st.max_min_diff) * 100.00 as normalized_score,
        risk_indicators
    from cosainto_data.rt_merch_level_score_output base
    join cosainto_data.rt_merch_level_score_stat st
    on 1 = 1
);

-- ------- [SCORING] Transaction Level Output
drop table if exists cosainto_data.rt_transaction_level_agg;
create table cosainto_data.rt_transaction_level_agg (PRIMARY KEY(merch_id)) as 
(
    select 
        merch_id,
        txn_id,
        txn_amt,
        cr_card_num,
        reason
    from cosainto_data.rt_skewed_txns
    union 
    select 
        merch_id,
        txn_id,
        txn_amt,
        cr_card_num,
        cast('high_amt_stlmnt' as varchar(40)) as reason
        from cosainto_data.rt_high_amt_sttlmnt
);