import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Attribute-based Mapping',
    description: (
      <>
        Define your database structure using PHP 8.3 attributes. Map classes to tables
        and properties to columns with ease and type safety.
      </>
    ),
  },
  {
    title: 'Integrated Query Builder',
    description: (
      <>
        Leverage the power of Aura.Sql-Query directly within your entities.
        Build complex queries with a fluent and intuitive API.
      </>
    ),
  },
  {
    title: 'Two-Level Caching',
    description: (
      <>
        Boost performance with a built-in caching system. Jinya Database uses both
        in-memory KeyCache and persistent FileCache for optimal speed.
      </>
    ),
  },
];

function Feature({title, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
