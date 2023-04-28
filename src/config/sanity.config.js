import { createClient } from 'next-sanity'

const client = createClient({
    projectId: 'rk9075i4',
    dataset: 'production',
    apiVersion: '2023-04-26',
    useCdn: false
})

export default client