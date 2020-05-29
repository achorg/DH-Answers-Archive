# DH Q&A dataset

This is a dataset generated from the archive of DH Q&amp;A website
content.

## CSV fields

File: [dhqa_data.csv](dhqa_data.csv)

Fields:

* *url* post permalink
* *topic_url* permalink for the topic this post belongs to
* *question* topic question
* *tags* list of tags for the topic
* *author* post author name
* *author_url* post author url
* *html_content* post content with as HTML
* *content* post content in plain text
* *date* date/time the post was published ISO 8601 format (if available)
* *relative_date* relative date text displayed in the HTML, e.g. "3 years ago"
* *snapshot_date* timestamp for the most recent capture of this page in the wayback
* *order* order of the post within the topic
* *is_best_answer* true if the post was marked as a best answer
* *reply_to* permalink for a post if this was a reply to a specific post

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



