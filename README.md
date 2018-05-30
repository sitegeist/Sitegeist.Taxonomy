# Sitegeist.Taxonomy

> Manage vocabularies and taxonomies in Neos as node in a separate subtree `/taxonomy` distinct from `/sites`

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## About

If taxonomies are defined as documents in the `/sites` subtree of the Neos ContentRepository things tend to get
complicated:

 - If the meaning of an item is determined by its position in a hierarchy it is complicated to add more than one
   relation or relations for different contexts.
 - Mixing Taxonomies (meaning) with content (presentation) leads to a complicated structure that often is hard to
   comprehend for editors.
 - It is hard to share taxonomies across multiple sites.
 - It is hard to ensure taxonomies exist in all needed dimensions since this affects the site structure as well.
 - Limit the read and write-access to taxonomies inside sites is possible but not trivial.

Sitegeist.Taxonomies stores vocabularies and taxonomies as nodes outside of the `/sites` hierarchy in the content
repository. This way the meaning of the taxonomy relations can be expressed better, taxonomies can be used across
multiple sites and the taxonomy documents can be defined without interfering with the taxonomy meaning.

it also provides a separate backend module for managing vocabularies and taxonmies.


## Storing vocabularies and taxonomies in the ContentRepository

Sitegeist.Taxonomy defines three basic node types:

- `Sitegeist.Taxonomy:Root` - The root node at the path `/taxonomy`, allows only vocabulary nodes as children
- `Sitegeist.Taxonomy:Vocabulary` - The root of a hierarchy of meaning, allows only taxonomies nodes as children   
- `Sitegeist.Taxonomy:Taxonomy` - Item in the hierarchy that represents a specific meaning, allows only taxonomy
  nodes as children

If you have to enforce the existence of a specific vocabulary or taxonomy you can use a derived node type:

```YAML
    Vendor.Site.Taxonomy.Root:
      superTypes:
        Sitegeist.Taxonomy:Root: TRUE
      childNodes:
        animals:
          type: 'Sitegeist.Taxonomy:Vocabulary'
```

And configure the taxonomy-package to use this root node type instead of the default:

```YAML
    Sitegeist:
      Taxonomy:
        contentRepository:
          rootNodeType: 'Vendor.Site.Taxonomy.Root'
          vocabularyNodeType: 'Sitegeist.Taxonomy:Vocabulary'
          taxonomyNodeType: 'Sitegeist.Taxonomy:Taxonomy'
```

## Referencing taxonomies

Since taxonomies are nodes, they are simply referenced via `reference` or `references` properties:

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

If you want to limit the selectable taxons to a vocalbulary or even a taxonomy then you can configure a more specific
startingPoint:

```YAML
    taxonomyReferences:
      ui:
        inspector:
          editorOptions:
            startingPoint: '/taxonomy/animals/mammals'
```

## Content-Dimensions

Vocabularies and Taxonomies will always be created in all base-dimensions. This way it is ensured that they can
always be referenced. The title and description of a taxons and vocabularies can be translated as is required for
the project.

## CLI Commands

The taxonomy package includes some cli commands for managing the taxonomies.

- `taxonomy:list` List all taxonomy vocabularies
- `taxonomy:import` Import taxonomy content, expects filename + vocabulary-name (with globbing)
- `taxonomy:export` Export taxonomy content, expects filename + vocabulary-name (with globbing)
- `taxonomy:prune` Prune taxonomy content, expects vocabulary-name (with globbing)

## Privileges

Sitegeist.Taxonomy brings the following privilege targets to allow you to restrict read access, mangement and editing
of taxonomies:

- `Sitegeist.Taxonomy:Module.Show` Show the backend Module and explore the existing taxonomies. By default granted to Editors.
- `Sitegeist.Taxonomy:Module.ManageVocabularyActions` Add, edit and delete vocabularies. By default granted to Administrators.
- `Sitegeist.Taxonomy:Module.ManageTaxonomyActions` Add, edit and delete taxons. By default granted to Administrators.

Reading and referencing taxonomies from other nodes is currently not limited.

## Installation

Sitegeist.Taxonomy is available via packagist. `"sitegeist/taxonomy" : "^1.0"` to the require section of the composer.json
or run `composer require sitegeist/taxonomy`.

We use semantic-versioning so every breaking change will increase the major-version number.

## Contribution

We will gladly accept contributions. Please send us pull requests.

## License

See [LICENSE](./LICENSE)
