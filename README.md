# sfPropelLuceneableBehaviorPlugin #

The `sfPropelLuceneableBehaviorPlugin` is a symfony plugin that enabled the model(s) to be searchable. This plugin use sfPropel15Plugin as an ORM.

## Installation ##
  * Install the plugin

        git clone git://github.com/nibsirahsieu/sfPropelLuceneableBehaviorPlugin.git

  * Activate the plugin in the `config/ProjectConfiguration.class.php`

        [php]
        class ProjectConfiguration extends sfProjectConfiguration
        {
          public function setup()
          {
            ...
            $this->enablePlugins('sfPropelLuceneableBehaviorPlugin');
            ...
          }
        }

## How to use ##
  * Example:

        [xml]
        <table name="section">
          <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
          <column name="title" type="VARCHAR" required="true" primaryString="true" />
          <column name="content" type="LONGVARCHAR" required="true" />
          <behavior name="luceneable" />
        </table>

     if you don't supply the parameter values, the behavior will index all columns using `UnStored` method except the primary key (primary key will be indexed Using `Keyword` method).

  * Customizing behavior (format parameter value => fieldMethod:boost, ex: text:1.8)

        [xml]
        <table name="section">
          <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
          <column name="title" type="VARCHAR" required="true" primaryString="true" />
          <column name="content" type="LONGVARCHAR" required="true" />
          <behavior name="luceneable" >
            <parameter name='id' value='keyword' />
            <parameter name='title' value='text:1.5' />
            <parameter name='content' value='unstored:1.5' />
            <parameter name='published_at' value='unindexed' />
          </behavior>
        </table>

## How to Search ##

  * pagination

        $pager = sfLuceneModelSearch::create($model, $query)->paginate($page=1, $limit = 10);

        [php]
        foreach ($pager->getResults() as $value)
        {
          echo $value->getTitle() . ',' . $value->getContent();
        }

  * non pagination
    
        $values = sfLuceneModelSearch::create($model, $query)->find($limit = 10);

        [php]
        foreach ($values as $value)
        {
          echo $value->getTitle() . ',' . $value->getContent();
        }

## Available tasks ##

      * php symfony propel:lucene-create-index
      * php symfony propel:lucene-optimize-index