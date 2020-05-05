#!/usr/bin/env python
# encoding: utf-8
'''
Script to parse data DH Q&A archive and create a dataset of posts.

Install python dependencies:

    pip install beautifulsoup4 feedparser requests

Clone DH Q&A archive repository:

https://github.com/achorg/DH-Answers-Archive

Run this script in the top-level directory of the repository.

'''


import csv
import datetime
import glob
import os
import re

from bs4 import BeautifulSoup, Comment
import feedparser
import requests

baseurl = 'http://digitalhumanities.org/answers'


def get_post_info(div, topic_url, feed):
    # take a post container and return dict of post info
    # takes  bs4 div, basec url for this topic, and rss feedparser obj

    info = {}

    # generate permalink from li id since in at least one
    # case the permalink isn't found

    info['url'] = '%s#%s' % (topic_url, div['id'])

    # first div id includes order information as position-#
    info['order'] = div.div['id'].split('-')[1]
    threadauthor = div.find('div', class_='threadauthor')
    author_url = threadauthor.a['href']
    # members have local profile urls
    if author_url.startswith('/'):
        author_url = '%s%s' % (baseurl, author_url)
    info['author_url'] = author_url
    info['author'] = threadauthor.find('strong').get_text()
    # question is in first threadpost
    threadpost = div.find('div', class_='threadpost')

    # remove 'tweet this question' block and related comments
    social = threadpost.find('div', class_="social-it")
    if social:
        social.extract()
    [comment.extract() for comment in threadpost.findAll(
        text=lambda text:isinstance(text, Comment))]

    # get post html content
    info['html_content'] = threadpost.div.prettify()

    # extract text from post on html page
    info['content'] = threadpost.find('div', class_='post').get_text()

    # check if this is a reply to a specific post
    if threadpost.p and threadpost.p.get_text().startswith('Replying to'):
        # name could be a link, so get last link in the reply p
        reply_to_post = threadpost.p.find_all('a')[-1]['href']
        info['reply_to'] = '%s%s' % (baseurl, reply_to_post)

    # check if marked as a best answer
    info['is_best_answer'] = bool(post.find('div', class_='best_answer'))

    # post date
    poststuff = div.find('div', class_='poststuff')
    if poststuff:
        relative_post_date = poststuff.text
        # Posted x years ago
        relative_post_date = relative_post_date.replace('Posted ', '') \
                                               .replace(' Permalink', '')
        info['relative_date'] = relative_post_date.strip()
    else:
        print('poststuff div not found for %(url)s' % info)

    # find RSS entry for this record if possible
    if feed:
        entries = [e for e in feed.entries if e.link == info['url']]
        if entries:
            entry = entries[0]
            # convert parsed timestruct into isoformat
            info['date'] = datetime.datetime(*entry.published_parsed[:6]) \
                                   .isoformat()
        else:
            print('ERROR: not in feed %s' % info['url'])

    return info


def wayback_machine_timestamp(url):
    '''get timestamp for most recent capture of a url from wayback
    machine api'''
    response = requests.get('http://archive.org/wayback/available',
                            params={'url': url})
    if response.status_code == requests.codes.ok:
        data = response.json()
        # if archived snapshots is not empty, return closest timestamp
        if data['archived_snapshots']:
            return data['archived_snapshots']['closest']['timestamp']


dhqa_posts = []

post_fieldnames = [
    'url',
    'topic_url',
    'question',
    'tags',
    'author',
    'author_url',
    'html_content',
    'content',
    'date',
    'relative_date',
    'snapshot_date',
    'order',
    'is_best_answer',
    'reply_to',
]

for path in glob.glob('topic/*/index.html'):

    # topic meta should include url for topic,
    # but is not completely reliable!
    # generate from filename instead
    topic_url = '%s/%s' % (baseurl, os.path.dirname(path))
    capture_date = wayback_machine_timestamp(topic_url)

    topic_data = {
        'topic_url': topic_url,
        'snapshot_date': capture_date or ''
    }

    with open(path) as topicdoc:
        soup = BeautifulSoup(topicdoc, 'html.parser')
        # page title is question (summary/brief)
        topic_data['question'] = soup.find('h2').get_text()
        tags = soup.find_all('a', rel='tag')
        topic_data['tags'] = ';'.join([t.get_text() for t in tags])
        # should tags apply to all posts or just question?

        # html doesn't have a proper date but RSS should
        # get rss filename from rss link
        rss = soup.find('a', class_="rss-link")['href'].lstrip('/')
        # print(rss)
        if os.path.exists(rss):
            # with open(rss) as rssdoc:
            feed = feedparser.parse(rss)
            # rss_soup = BeautifulSoup(rssdoc, 'lxml')
            # items = rss_soup.findAll('item')
            if not feed.entries:
                print('ERROR: RSS file has no content: %s' % rss)
                feed = None
        else:
            print('ERROR: Missing RSS file: %s' % rss)
            feed = None

        posts = soup.findAll('li', id=re.compile(r'^post-\d+'))
        for post in posts:
            post_data = get_post_info(post, topic_url, feed)
            post_data.update(topic_data)
            dhqa_posts.append(post_data)

        # check for second page (few cases; nothing has more than 2 pages)
        next_link = soup.find('a', class_='next')
        if next_link:
            page_two = '%s/index.html' % next_link['href'].lstrip('/')
            with open(page_two) as page_two_doc:
                soup2 = BeautifulSoup(page_two_doc, 'html.parser')
                posts = soup2.findAll('li', id=re.compile(r'^post-\d+'))
                for post in posts:
                    # unlikely these will be in RSS feed...
                    post_data = get_post_info(post, topic_url, feed)
                    post_data.update(topic_data)
                    dhqa_posts.append(post_data)

        # NOTE: missing 11 topic RSS feeds
        # may be able to get date from tag feeds

print('%d posts total' % len(dhqa_posts))


with open('dhqa_data.csv', 'w') as outfile:
    writer = csv.DictWriter(outfile, fieldnames=post_fieldnames)
    writer.writeheader()
    writer.writerows(dhqa_posts)