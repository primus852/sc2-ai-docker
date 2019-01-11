from setuptools import setup, find_packages

with open('README.rst') as f:
    readme_file = f.read()

with open('LICENSE') as f:
    license_file = f.read()

setup(
    name='agent',
    version='0.0.2',
    description='A custom Agent for SC2-AI for reinforced learning',
    long_description=readme_file,
    author='Torsten Wolter',
    author_email='tow.berlin@gmail.com',
    url='https://github.com/primus852/sc2-ai-docker',
    license=license_file,
    packages=find_packages(exclude=('tests', 'docs')),
    install_requires=[
        'pytz',
        'pandas',
        'numpy',
        'pysc2',
        'sc2',
        'PyMySQLdb',
        'keras',
        'tensorflow',
        'multiprocessing'
        'SQLAlchemy',
        'SQLAlchemy-Utils'
    ]
)
