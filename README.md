# Sitegeist.Taxonomy
### Manage Vocabularies and Taxonomies in Neos as Node in a separate tree `/taxonomy` distinct from `/sites`

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## About 

If taxonomies are defined in the `/sites` of the Neos ContentRepository as documents things tend to get complicated:
 - If the meaning of an item is determined by its position in a hierarchy it is complicated add more than one relation or relations for differenz contexts.
 - Mixing Taxonomies (meaning) with content (presentation) leads to a complicated structure that often is hard to get 
   for editors. 
 - It is hard to share taxonomies across multiple sites.
 - It is hard to ensure taxonomies exist in all needed dimensions since this affects the site structure aswell.
 - Limit the read and write-access to taxonimies inside sites is possible but not trivial.

This package stores vocabularies and taxonomies as nodes outside of the `/sites` hierarchy in the content repository. That 
way the meaning of the taxonomy relations can be expressed better, taxonomies can be used across multiple sites and the 
documents can be defined without interfering with the taxonomy meaning.

To manage vocabularies and taxonmies a separate backend module is provided. 

## Status

**This is currently experimental code so do not rely on any part of this.**


## Storing Vocabularies and Taxonomies in the ContentRepository

This package defines three different NodeTypes:

- `Sitegeist.Taxonomy:Root` - The root node at the path `/taxonomy`, allows only vocabulary-nodes as children   
- `Sitegeist.Taxonomy:Vocabulary` - The root of a hierarchy of meaning), allows only taxonomies nodes as children   
- `Sitegeist.Taxonomy:Taxonomy` - Item in the hierarchy that represents a specific meaning, allows only taxonomies nodes as children   

If you have to enforce the existence of a specific vocabulary or taxonomy you can use a derived node-type:

```YAML
    Vendor.Site.Taxonomy.Root:
      superTypes:
        Sitegeist.Taxonomy:Root: TRUE
      childNodes:
        animals:
          type: 'Sitegeist.Taxonomy:Vocabulary'
```

And configure the taxonomy-package to use this rootNodeType instead of the default:

```YAML
    Sitegeist:
      Taxonomy:
        contentRepository:
          rootNodeType: 'Vendor.Site.Taxonomy.Root'
          vocabularyNodeType: 'Sitegeist.Taxonomy:Vocabulary'
          taxonomyNodeType: 'Sitegeist.Taxonomy:Taxonomy'
```

## Referencing to taxonomies

Since taxonomies are nodes they are referenced via reference or references node-properties.

```YAML
    taxonomyReferences:
      type: references
      ui:
        label: 'Taxonomy References'
        inspector:
          group: taxonomy
          editorOptions:
            nodeTypes: ['Sitegeist.Taxonomy:Taxonomy']
            startingPoint: '/taxonomy'
            placeholder: 'assign Taxonomies'
```

If you want to limit the selectable taxons to a vocalbulary or even a taxonomy use a more specific startingPoint.

```YAML
    taxonomyReferences:
      ui:
        inspector:
          editorOptions:
            startingPoint: '/taxonomy/animals/mammals'
```

## Content-Dimensions 

Vocabularies and Taxonomies will be always be created in all base-dimensions. That way it is ensured that they can 
always be referenced. The title and descrioption of a taxons and vocabularies can be translated as is required for
the project.    

## Priviledges

The package brings the following privilege-targets to allow controlling the editing of taxonomies.

- `Sitegeist.Taxonomy:Module.Show` Show the backend Module and explore the existing taxonomies. By default granted to Editors. 
- `Sitegeist.Taxonomy:Module.ManageVocabularyActions` Add, edit and delete vocabularies. By default granted to Administrators. 
- `Sitegeist.Taxonomy:Module.ManageTaxonomyActions` Add, edit and delete taxons. By default granted to Administrators. 

Reading and referencing to taxonomies from other nodes is not limited at the current time. 

## Contribution

We will gladly accept contributions. Please send us pull requests.
