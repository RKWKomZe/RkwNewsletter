# rkw_newsletter
## Features
Extension for sending newsletters. Each newsletter can contain several topics, which can be freely selected by the subscribers.
In the dispatch process, each subscriber then receives only the topics that he or she has chosen.

Special issues can be created.

## Structure and process
Editors can mark content in the backend as relevant for the newsletter.
It is then automatically collected and copied into the editorial template a few days before the send date, reducing the editor's workload for the newsletter.

A folder ("issue-folder") must be created for each newsletter topic.
Here the extention creates a new page a few days before the dispatch date and collects the marked contents there.
Using appropriate roles and approval processes, each topic can be prepared accordingly.

Each newsletter can have a general editorial that is sent to those who have subscribed to more than one topic.
In addition, each topic can have an editorial that is used only when a person subscribes to that topic only.

The extension includes a multi-step approval process. A timed release can be set on the lower release levels.

In the dispatch process, each subscribing person then receives only the topics that he or she has selected.
In the process, the topics are zipped together while maintaining the order selected by the editor.

## Setup
* Create a newsletter record with corresponding topics.
* A folder has to be created in the backend for each topic. The issues per topic will be generated into this.
* There is a backend layout for editing the newsletter articles, which can be set via  page configuration of the issue-folders.
* A cronjob must be created for the time-controlled creation of the issues, the approval process and the dispatch process.


Status of editing: 2022-05-10