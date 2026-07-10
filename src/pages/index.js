import DarkTheme from '../layouts/Dark'
import Home1 from "./home"
import SEO from "../components/SEO"

export default function Home() {
  return (
    <DarkTheme>
      <SEO
        title="Home"
        description="Pixels Soft is a creative digital agency offering web design, mobile development, graphic design, and digital marketing services."
        canonical="/"
      />
      <Home1 />
    </DarkTheme>
  )
}
