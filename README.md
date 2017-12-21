# Sitegeist.Taxonomy
### Manage Vocabularies and Taxonomies in Neos that are stored in the ContenRepository as seperate tree `/taxonomy` distinct from `/sites`

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## About 

If taxonomies are defined in the /sites of the Neos CR as Documents things tend to get complicated if multiple hierarchies 
for differnt context exist. Also is often not desirable to have the taxnonomies in the site hierarchy to avoid confusion 
for the editors, also we often have to limit the read and write-access to taxonimies seperately.   

This package stores vocabularies and taxonimie as nodes outside of the /sites hierarchy in the content repository. That 
way the meaning of the taxonomy relations can be expressed better, taxonomies can be used across multiple sites and the 
document can be defined without interfering with the taxonomy meaning.

To manage vocabularies and taxonmies a seperate backend module is provided. 

## Status

**This is currently experimental code so do not rely on any part of this.**


## Storing Vocabularies and Taxonomies in the ContentRepository

This package defines three different NodeTypes:

- `Sitegeist.Taxonomy:Root` - the root node with the path `/taxonomy` can contain   
- `Sitegeist.Taxonomy:Vocabulary` -   
- `Sitegeist.Taxonomy:Taxonomy`

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
``             

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


## Contribution

We will gladly accept contributions. Please send us pull requests.
