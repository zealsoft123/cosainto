drop table if exists base_table;
create table base_table as
(
select 
    'NA' as merch_id,
    txn_id,
    txn_type,
    txn_status,
    sttlmnt_dt,
    auth_amt,
    sttlmnt_amt,
    refund_txn_id,
    payment_type,
    card_type,
    cc_number,
    billing_postal_cd,
    billing_country,
    shipping_postal_cd,
    shipping_country,
    ip_addr,
    processor_response_code,
    sttlmnt_currency
    from base_table_temp t1
);


drop table if exists cos_merch_base_settled_txns;
create table cos_merch_base_settled_txns as 
(
    select
        t1.*,
        t1.total_amt*1.0000 / t1.total_txns*1.0000 as asp
        from 
        (
            SELECT
                merch_id,
                sttlmnt_dt,
                count(distinct(txn_id)) as total_txns,
                sum(sttlmnt_amt) as total_amt,
                max(sttlmnt_amt) as max_sttlmnt_amt,
                min(sttlmnt_amt) as min_sttlmnt_amt
            from base_table
            where txn_Status = 'settled'
            group by 1,2
        )t1
);
        
drop table if exists cos_merch_base_settled_cc_txns;
create table cos_merch_base_settled_cc_txns as 
(
    select
        t1.*,
        t1.total_amt*1.0000 / t1.total_txns*1.0000 as cc_asp
        from 
        (
			SELECT
				merch_id,
				sttlmnt_dt,
				substr(cc_number,1,6) as cr_card_bin,
				count(distinct(txn_id)) as total_txns,
				sum(sttlmnt_amt) as total_amt,
				max(sttlmnt_amt) as max_sttlmnt_amt,
				min(sttlmnt_amt) as min_sttlmnt_amt
			from base_table
			where txn_status = 'settled'
			group by 1,2,3
		)t1
);

-- [RULE] CONCENTRATED BIN TXNS - X% more txns processed on the same bin [LOGIC]
drop table if exists cos_rule_concc_summary;
create table cos_rule_concc_summary as
(
    SELECT
        base.merch_id,
        base.sttlmnt_dt,
        base.cr_card_bin,
        base.total_amt,
        base.total_txns as cc_txns,
        total.total_txns as total_txns,
        cast(base.total_txns as decimal(10,2)) / total.total_txns * 1.00 as card_txn_ratio
        from cos_merch_base_settled_cc_txns base
        join cos_merch_base_settled_txns total
        on base.merch_id = total.merch_id
        and base.sttlmnt_dt = total.sttlmnt_dt
    where total.total_txns >= 3 
);

drop table if exists cos_rule_concc_bin_txn;
create table cos_rule_concc_bin_txn as 
(
    SELECT 
        merch_id,
        sttlmnt_dt,
        max(card_txn_ratio) as max_card_txn_ratio
        from cos_rule_concc_summary
    group by 1,2
);

drop table if exists cos_rule_concc_flag_amt;
create table cos_rule_concc_flag_amt as 
(
    SELECT
        base.merch_id,
        base.sttlmnt_dt,
        max(base.total_amt) as total_bin_txns_amt
    from cos_rule_concc_summary base
    join cos_rule_concc_bin_txn total
    on base.card_txn_ratio = total.max_card_txn_ratio
    and base.sttlmnt_dt = total.sttlmnt_dt
    group by 1,2
);

-- [RULE] high ASP
drop table if exists cos_rule_high_asp;
create table cos_rule_high_asp as 
(
    SELECT
        base.merch_id,
        base.sttlmnt_dt,
        txn.txn_id,
        txn.sttlmnt_amt*1.0000 - base.asp*1.0000 as asp_diff_amt,
        case when txn.sttlmnt_amt > base.asp * 1.5  then 'Y' else 'N' end as high_asp_txn_flag
    from cos_merch_base_settled_txns base
    join base_table txn
    on base.merch_id = txn.merch_id
    and base.sttlmnt_dt = txn.sttlmnt_dt
    where base.total_amt >= 100
    and base.total_txns >= 3 
);

-- [RULE] high outlier
drop table if exists cos_rule_high_outlier;
create table cos_rule_high_outlier as 
(
    SELECT
        base.merch_id,
        base.sttlmnt_dt,
        txn.txn_id,
        txn.sttlmnt_amt*1.0000 - base.asp*1.0000 as asp_diff_amt,
        case when txn.sttlmnt_amt > base.asp * 3 then 'Y' else 'N' end as high_outlier_flag
    from cos_merch_base_settled_txns base
    join base_table txn
    on base.merch_id = txn.merch_id
    and base.sttlmnt_dt = txn.sttlmnt_dt
    where base.total_amt >= 100
    and base.total_txns >= 2   
);

-- [RULE] same dollar amount txns
drop table if exists cos_rule_same_dollar_amt_temp;
create table cos_rule_same_dollar_amt_temp as 
(
    SELECT
        merch_id,
        sttlmnt_dt,
        sttlmnt_amt,
        count(distinct(txn_id)) as total_same_amt_txn
    from base_table
    where sttlmnt_amt >= 10
    group by 1,2,3
);

drop table if exists cos_rule_same_dollar_amt;
create table cos_rule_same_dollar_amt as 
(
    SELECT 
        base.merch_id,
        base.sttlmnt_dt,
        base.total_same_amt_txn,
        txn.total_txns,
        case when base.total_same_amt_txn >= 0.5 * total_txns then 'Y' else 'N' end as same_dollar_amt_flag 
    from cos_rule_same_dollar_amt_temp base
    join cos_merch_base_settled_txns txn
    on base.merch_id = txn.merch_id
    and base.sttlmnt_dt = txn.sttlmnt_dt
    where base.sttlmnt_amt >= 10
    and txn.total_txns >= 3
);	

-- [RULE] daily EG
drop table if exists cos_past_day_txn;
create table cos_past_day_txn as 
(
    SELECT 
        merch_id,
        sttlmnt_dt,
        total_txns as past_day_txns,
        total_amt as past_day_amt,
        max_sttlmnt_amt,
        min_sttlmnt_amt
    from cos_merch_base_settled_txns
    where sttlmnt_dt = (
        select 
            max(sttlmnt_dt)
        from cos_merch_base_settled_txns
        where sttlmnt_dt not in 
                (
                    select 
                        max(sttlmnt_dt)
                    from cos_merch_base_settled_txns
                )
        )
);

drop table if exists cos_rule_daily_eg;
create table cos_rule_daily_eg as 
(
    SELECT
        base.merch_id,
        case when base.total_txns > eg.past_day_amt * 1.5 then 'Y' else 'N' end as high_eg_flag
    from cos_merch_base_settled_txns base
    left join cos_past_day_txn eg
    on base.merch_id = eg.merch_id
    where base.sttlmnt_dt = (
        select 
            max(sttlmnt_dt)
        from cos_merch_base_settled_txns
    )
    and eg.past_day_amt >= 100 
    and base.total_txns >= 3 
);

-- [RULE] Weekly EG
drop table if exists cos_past_2wk_txn;
create table cos_past_2wk_txn as 
(
    SELECT 
        merch_id,
        sttlmnt_dt,
        avg (total_txns) as avg_total_txn,
        avg (total_amt) as avg_total_amt,
        max(max_sttlmnt_amt) as max_2wk_sttlmnt_amt,
        min(min_sttlmnt_amt) as min_2wk_sttlmnt_amt
    from cos_merch_base_settled_txns
    where sttlmnt_dt between current_date-14 and current_date-1    
	group by 1,2
);

drop table if exists cos_rule_wk_eg;
create table cos_rule_wk_eg as 
(
    SELECT
        base.merch_id,
        case when base.total_txns > wk.avg_total_amt * 3 then 'Y' else 'N' end as high_wk_flag
    from cos_merch_base_settled_txns base
    left join cos_past_2wk_txn wk
    on base.merch_id = wk.merch_id
    where base.sttlmnt_dt = (
        select 
            max(sttlmnt_dt)
        from cos_merch_base_settled_txns
    )
    and wk.avg_total_amt >= 10
    and base.total_txns >= 3 
);


-- [RULE] High settlement amount
drop table if exists cos_rule_high_sttlmnt_amt;
create table cos_rule_high_sttlmnt_amt as 
(
    SELECT
        base.merch_id,
        base.sttlmnt_dt,
        base.txn_id,
        case when base.sttlmnt_amt > 1.5*txn.max_2wk_sttlmnt_amt then 'Y' else 'N' end as high_sttlmnt_amt_flag
    from base_table base
    join cos_past_2wk_txn txn
    on base.merch_id = txn.merch_id
    where base.sttlmnt_dt = (
        select 
            max(sttlmnt_dt)
        from cos_merch_base_settled_txns
    )
);


-- [SUMMARY] consolidating the rules table together
drop table if exists cos_cons_txn_view1; 
create table cos_cons_txn_view1 as 
(
    SELECT
        base.merch_id,
        base.txn_id,
        base.txn_status,
        base.txn_type,
        base.sttlmnt_amt as amount,
        base.sttlmnt_dt as settlement_date,
        asp.high_asp_txn_flag,
        asp.asp_diff_amt,
        outlier.high_outlier_flag,
        same_dol.same_dollar_amt_flag,
        high_sttl.high_sttlmnt_amt_flag
        from base_table base
        left join cos_rule_high_asp asp
        on base.merch_id = asp.merch_id and base.txn_id = asp.txn_id
        left join cos_rule_high_outlier outlier
        on base.merch_id = outlier.merch_id and outlier.txn_id = asp.txn_id
        left join cos_rule_same_dollar_amt same_dol
        on base.merch_id = same_dol.merch_id -- and same_dol.txn_id = asp.txn_id
        left join cos_rule_high_sttlmnt_amt high_sttl
        on base.merch_id = high_sttl.merch_id and high_sttl.txn_id = asp.txn_id        
        where txn_status = 'settled'
        and txn_type = 'sale'
);


drop table if exists cos_cons_txn_view2; 
create table cos_cons_txn_view2 as 
(
    SELECT
        merch_id,
        txn_id,
        txn_status,
        txn_type,
        settlement_date,
        case when high_asp_txn_flag = 'Y' then 10 else 0 end as flag_asp_score,
        case when high_asp_txn_flag = 'Y' and asp_diff_amt > 0 then asp_diff_amt * 0.01 else 0 end as flag_asp_multipler,
        case when high_outlier_flag = 'Y' then 15 else 0 end as flag_outlier_score,
        case when same_dollar_amt_flag = 'Y' then 8 else 0 end as flag_same_dollar_amt,
        case when same_dollar_amt_flag = 'Y' then 12 else 0 end as flag_high_sttlmnt_amt
    from cos_cons_txn_view1
);

drop table if exists cos_cons_txn_score;
create table cos_cons_txn_score as 
(
    SELECT
        merch_id,
        txn_id,
        txn_status,
        txn_type,
        settlement_date,
        risk_score,
        case
            when flag_asp_score > 0 and  flag_outlier_score > 0 and flag_same_dollar_amt > 0 then 'Hybrid'
            when flag_asp_score > 0 or flag_high_sttlmnt_amt > 0 then 'credit'
            when flag_outlier_score > 0 or flag_same_dollar_amt > 0 then 'fraud' 
            when risk_score= 0 then 'no risk'
        else 'fraud' end as risk_reason
    from 
        (
        SELECT
            merch_id,
            txn_id,
            txn_status,
            txn_type,
            settlement_date,
            flag_asp_score,
            flag_outlier_score,
            flag_same_dollar_amt,
            flag_high_sttlmnt_amt,
            flag_asp_multipler,
            flag_asp_score + flag_asp_multipler + flag_outlier_score + flag_same_dollar_amt + flag_high_sttlmnt_amt + flag_asp_multipler as risk_score
        from cos_cons_txn_view2
        ) t1
);


