# DH Q&A dataset

This is a dataset generated from the archive of DH Q&amp;A website
content.

## CSV fields

File: [dhqa_data.csv](dhqa_data.csv)

Fields:

* url
* topic_url
* question
* tags
* author
* author_url
* html_content
* content
* date
* relative_date
* snapshot_date
* order
* is_best_answer
* reply_to


## Wayback Machine capture data

To help determine dates for posts that are not present in the RSS feeds
available in the site archive, we downloaded data for topic and rss
URLs from the Internet Archive [Wayback CDX Server API](https://github.com/internetarchive/wayback/tree/master/wayback-cdx-server).

Fields:

* urlkey
* timestamp
* original (original url)
* mimetype
* statuscode
* digest
* length

Files:

* [topic urls](wayback_cdx_topics.json)
* [rss urls](wayback_cdx_rss.json)



