jQuery(document).ready(function($){

    var Topic =  Backbone.Model.extend({
        defaults: {
            topic_title: '',
            topic_content: '',
            topic_forum :'0',
        },

        validate: function(attrs) {           
            var errors = this.errors = {};

            if (!attrs.topic_title) errors.topic_title = wpwaForumMemberData.nameRequired;
            if (attrs.topic_content == '') errors.topic_content = wpwaForumMemberData.descRequired;
            if (attrs.topic_forum == '0') errors.topic_forum = wpwaForumMemberData.forumRequired;

            if (!_.isEmpty(errors)){
                console.log(errors);
                return errors;
            }
        }
    });

    var TopicCollection = Backbone.Collection.extend({
        model: Topic,
        url: wpwaForumMemberData.ajaxUrl+"?action=wpwaf_process_topics&forum_member_id="+wpwaForumMemberData.memberID
    });

    var topicsList;
    var TopicListView = Backbone.View.extend({
        el: $('#forum_member_topics'),
        template : _.template($('#topic-list-template').html()),
        initialize: function () {
            topicsList = new TopicCollection();
            topicsList.bind("change", _.bind(this.getData, this));
            this.getData();

        },
        getData: function () {
            var obj = this;
            topicsList.fetch({
                success: function () {
                    obj.render();
                }
            });
        },
        render: function () {  
            var header_data = $('#topic-list-header').html();
            
            template_data = this.template({ topics: topicsList.toJSON() });
            $(this.el).find("#wpwaf_list_topics").html(header_data+template_data);
            return this;
        },
        events: {
            'click #wpwaf_add_topic': 'addNewTopic',
            'click #wpwaf_topic_create': 'saveNewTopic'
        },
        addNewTopic: function(event) {
            $("#wpwaf_topic_add_panel").show();
           
        },
        saveNewTopic: function(event) {
            var options = {
                success: function (response) {
                    if("error" == response.changed.status){
                        // console.log(response.changed.msg)
                    }
                },
                error: function (model, error) {
                    
                    console.log(error);
                }
            };

            var topic = new Topic();

            var topic_title = $("#wpwaf_topic_title").val();
            var topic_content = $("#wpwaf_topic_content").val();
            var topic_forum = $("#wpwaf_topic_forum").val();
            var forum_member_id = $("#wpwaf_topic_forum_member").val();
    
            topicsList.add(topic);

            topicsList.create({
                topic_title: topic_title,
                topic_content:topic_content,
                topic_forum : topic_forum,
                forum_member_id : forum_member_id
            },options);

        }
    });

    var topicView  = new TopicListView();


});



